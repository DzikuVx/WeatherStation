'''
Following script gets weather data from OpenWeathcerMap.org using 
JSON API 

Data acquisited:
- Pressure
- Wind speed
- Wind Direction

'''
import urllib2, json, sqlite3, os
import Adafruit_BMP.BMP085 as BMP085
import sensor

from openweatherconfig import config

def fetchJSON(url):
    req = urllib2.Request(url)
    response = urllib2.urlopen(req)
    return response.read()


def processData(json):
    out = {}

    sensor_handler = sensor.sensor()

    bmp_180_sensor = BMP085.BMP085()

    out['pressure'] = round(bmp_180_sensor.read_sealevel_pressure(35) / 100, 1)
    out['wind-direction'] = json["wind"]["deg"]
    out['wind-speed'] = json["wind"]["speed"]

    # FIXME this is a hack, whole process should be rewritten
    sensor_handler.save_value(2, round(bmp_180_sensor.read_sealevel_pressure(35) / 100, 1))
    sensor_handler.save_value(3, json["main"]["pressure"])
    sensor_handler.save_value(4, json["wind"]["speed"])
    sensor_handler.save_value(5, json["wind"]["deg"])
    sensor_handler.save_value(6, bmp_180_sensor.read_temperature()) # Save internal temperature readout
    return out

def saveSQLite(data):
    conn = sqlite3.connect(os.path.dirname(os.path.realpath(__file__)) + '/data.db')

    c = conn.cursor()
    c.execute('CREATE TABLE IF NOT EXISTS external_data(`Date` text, Pressure int, WindSpeed real, WindDirection int)')
    c.execute(
        "INSERT INTO external_data(`Date`, Pressure, WindSpeed, WindDirection) VALUES(datetime('now','localtime'), " + str(
            data['pressure']) + "," + str(data['wind-speed']) + "," + str(data['wind-direction']) + ")")

    conn.commit()
    conn.close()


def main():
    sWeather = fetchJSON(
        "http://api.openweathermap.org/data/2.5/weather?q=" + config['location'] + "&units=metric&APPID=" + config[
            'api'])
    jWeather = json.loads(sWeather)

    data = processData(jWeather)

    saveSQLite(data)

    print data


if __name__ == "__main__":
    main()
