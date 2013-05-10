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

def getReadout(type):

	if type == "internal":
		p = subprocess.Popen('sudo /home/pi/raspberry_temperature_log/sensor_driver 11 4', shell=True, stdout=subprocess.PIPE, stderr=subprocess.STDOUT)
	elif type == "external":
		p = subprocess.Popen('sudo /home/pi/raspberry_temperature_log/sensor_driver 2302 17', shell=True, stdout=subprocess.PIPE, stderr=subprocess.STDOUT)
	else:
		return None

	for line in p.stdout.readlines():

		if len(line) < 2 or len(line) > 10:
			print "Error from driver: " + line
			return None
		else:
			return line.split("|")



def saveSQLite(data, type):
	
	conn = sqlite3.connect(os.path.dirname(os.path.realpath(__file__)) + '/data.db')

	if type == "internal":

		c = conn.cursor()
		c.execute('CREATE TABLE IF NOT EXISTS readouts(`Date` text, Temperature real, Humidity real)')

		c.execute("INSERT INTO readouts(`Date`, Humidity, Temperature) VALUES(datetime('now','localtime'), "+data[0]+","+data[1]+")")

	elif type == "external":
		c = conn.cursor()
		c.execute('CREATE TABLE IF NOT EXISTS readouts_external(`Date` text, Temperature real, Humidity real)')

		c.execute("INSERT INTO readouts_external(`Date`, Humidity, Temperature) VALUES(datetime('now','localtime'), "+data[0]+","+data[1]+")")
	else:
		return

	conn.commit()
	conn.close()
	
def main():

	print "Internal Sensor:"

	readout = None

	counter = 0

	while (readout == None and counter < 5):

		counter += 1 

		readout = getReadout("internal")

		if readout != None:

			saveSQLite(readout, "internal")

			humidity = readout[0]
			temperature = readout[1]
		
			print "Humidity: " + humidity
			print "Temperature: " + temperature
		else:
			time.sleep(1)

	print ""
	print "External Sensor:"

	readout = None

	counter = 0

	while (readout == None and counter < 5):

		counter += 1 

		readout = getReadout("external")

		if readout != None:

			saveSQLite(readout, "external")

			humidity = readout[0]
			temperature = readout[1]
		
			print "Humidity: " + humidity
			print "Temperature: " + temperature
		else:
			time.sleep(1)

if __name__ == "__main__":
    main()
