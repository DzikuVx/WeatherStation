#!/usr/bin/env python
# -*- coding: utf-8 -*- 

'''
Raspberry Pi Temperature and Humidity logger using DHT22 Sensor
Paweł Spychalski
http://shtr.eu
'''

import time, os
import sqlite3
import pigpio
import DHT22

def saveSQLite(data):
	
	conn = sqlite3.connect(os.path.dirname(os.path.realpath(__file__)) + '/data.db')

	c = conn.cursor()
	c.execute('CREATE TABLE IF NOT EXISTS readouts_external(`Date` text, Temperature real, Humidity real)')
	c.execute("INSERT INTO readouts_external(`Date`, Humidity, Temperature) VALUES(datetime('now','localtime'), " + str(data[0]) + "," + str(data[1]) + ")")

	conn.commit()
	conn.close()
	
def main():

	print "External Sensor:"

	readout = None

	counter = 0

	pi = pigpio.pi()
	sensor = DHT22.sensor(pi, 17)

	while (readout == None and counter < 5):

		counter += 1 

		#Get data from sensor
		sensor.trigger()
		time.sleep(0.2)

		humidity = sensor.humidity()
		temperature = sensor.temperature()

		if humidity != None and temperature != None and humidity >= 0 and humidity <= 100:

			readout = [humidity, temperature]

			saveSQLite(readout)

			print "Humidity: " + str(humidity)
			print "Temperature: " + str(temperature)
			counter = 10
		else:
			time.sleep(5)

if __name__ == "__main__":
    main()
