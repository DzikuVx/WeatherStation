import urllib2, json, sqlite3, os

from openweatherconfig import config

def fetchJSON(url):
    req = urllib2.Request(url)
    response=urllib2.urlopen(req)
    return response.read()

def processData(json):

	out = {}
	out['pressure'] = json["main"]["pressure"]
	out['wind-direction'] = json["wind"]["deg"]
	out['wind-speed'] = json["wind"]["speed"]

	return out

def saveSQLite(data):
	
	conn = sqlite3.connect(os.path.dirname(os.path.realpath(__file__)) + '/data.db')

	c = conn.cursor()
	c.execute('CREATE TABLE IF NOT EXISTS external_data(`Date` text, Pressure int, WindSpeed real, WindDirection int)')
	c.execute("INSERT INTO external_data(`Date`, Pressure, WindSpeed, WindDirection) VALUES(datetime('now','localtime'), "+str(data['pressure'])+","+str(data['wind-speed'])+","+str(data['wind-direction'])+")")
	
	conn.commit()
	conn.close()

def main():

	sWeather = fetchJSON("http://api.openweathermap.org/data/2.5/weather?q="+config['location']+"&units=metric&APPID="+config['api'])
	jWeather = json.loads(sWeather)

	data = processData(jWeather)

	saveSQLite(data)

	print data

if __name__ == "__main__":
	main()