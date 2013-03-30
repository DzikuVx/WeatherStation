#!/usr/bin/env python
# -*- coding: utf-8 -*- 

'''
Raspberry Pi Temperature and Humidity logger using DHT11 Sensor
Paweł Spychalski
http://www.spychalski.info
'''

import subprocess
import MySQLdb
import time

db_host = "localhost"
db_user = "pi_temperature"
db_password = "pi_temperature"
db_name = "pi_temperature"

def saveDebug(line):
	connection = MySQLdb.connect(db_host, db_user, db_password, db_name)

	connection.begin()

	cursor = connection.cursor()
	cursor.execute("INSERT INTO `debug`(`text`) VALUES('"+line+"')")

	connection.commit()

def getReadout():
	p = subprocess.Popen('sudo /home/pi/raspberry_temperature_log/dht11_sensor', shell=True, stdout=subprocess.PIPE, stderr=subprocess.STDOUT)
	
	for line in p.stdout.readlines():
		saveDebug(line)

		if len(line) < 2:
			return None
		else:
			return line.split("|")


def saveReadout(data):
	connection = MySQLdb.connect(db_host, db_user, db_password, db_name)

	connection.begin()

	cursor = connection.cursor()
	cursor.execute("INSERT INTO `readouts`(Humidity,Temperature) VALUES("+data[0]+","+data[1]+")")

	connection.commit()

def main():

	readout = None

	while (readout == None):

		readout = getReadout()

		if readout != None:

			saveReadout(readout)

			humidity = readout[0]
			temperature = readout[1]
		
			print "Humidity: " + humidity
			print "Temperature: " + temperature
		else:
			time.sleep(1)

if __name__ == "__main__":
    main()



