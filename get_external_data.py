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

from openweatherconfig import config

db_connection = None


def fetchJSON(url):
    req = urllib2.Request(url)
    response = urllib2.urlopen(req)
    return response.read()


def processData(json):
    out = {}

    sensor = BMP085.BMP085()

    out['pressure'] = round(sensor.read_sealevel_pressure(35) / 100, 1)
    out['wind-direction'] = json["wind"]["deg"]
    out['wind-speed'] = json["wind"]["speed"]

    # FIXME this is a hack, whole process should be rewritten
    save_value(2, round(sensor.read_sealevel_pressure(35) / 100, 1))
    save_value(3, json["main"]["pressure"])
    save_value(4, json["wind"]["speed"])
    save_value(5, json["wind"]["deg"])

    return out


# FIXME create a module from it!
def save_value(sensor, value):
    global db_connection

    if db_connection is None:
        db_connection = sqlite3.connect(os.path.dirname(os.path.realpath(__file__)) + '/data-new.db')

    c = db_connection.cursor()
    c.execute('CREATE TABLE IF NOT EXISTS sensor_values(`Date` integer, `Sensor` integer, `Value` real)')
    c.execute('CREATE INDEX IF NOT EXISTS SENSOR_A ON sensor_values(`Date`, `Sensor`)')
    c.execute('CREATE INDEX IF NOT EXISTS SENSOR_B ON sensor_values(`Sensor`)')
    c.execute("INSERT INTO sensor_values(`Date`, `Sensor`, `Value`) VALUES(datetime('now','localtime'), " + str(
        sensor) + "," + str(value) + ")")

    db_connection.commit()


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
