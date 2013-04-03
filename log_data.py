#!/usr/bin/env python
# -*- coding: utf-8 -*- 

'''
Raspberry Pi Temperature and Humidity logger using DHT11 Sensor
Paweł Spychalski
http://www.spychalski.info
'''

import subprocess
import time
import sqlite3
import os

db_host = "localhost"
db_user = "pi_temperature"
db_password = "pi_temperature"
db_name = "pi_temperature"

def getReadout():
	p = subprocess.Popen('sudo /home/pi/raspberry_temperature_log/dht11_sensor', shell=True, stdout=subprocess.PIPE, stderr=subprocess.STDOUT)
	
	for line in p.stdout.readlines():

		if len(line) < 2 or len(line) > 5:
			print "Error from driver: " + line
			return None
		else:
			return line.split("|")



def saveSQLite(data):
	conn = sqlite3.connect(os.path.dirname(os.path.realpath(__file__)) + '/data.db')

	c = conn.cursor()
	c.execute('CREATE TABLE IF NOT EXISTS readouts(`Date` text, Temperature int, Humidity int)')

	c.execute("INSERT INTO readouts(`Date`, Humidity, Temperature) VALUES(datetime('now','localtime'), "+data[0]+","+data[1]+")")

	conn.commit()
	conn.close()

def main():

	readout = None

	counter = 0

	while (readout == None and counter < 5):

		counter += 1 

		readout = getReadout()

		if readout != None:

			saveSQLite(readout)

			humidity = readout[0]
			temperature = readout[1]
		
			print "Humidity: " + humidity
			print "Temperature: " + temperature
		else:
			time.sleep(1)

if __name__ == "__main__":
    main()
