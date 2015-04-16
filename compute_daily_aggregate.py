#!/usr/bin/env python
# -*- coding: utf-8 -*- 

'''
Raspberry Pi Temperature and Humidity logger using DHT11 Sensor
Paweł Spychalski
http://www.spychalski.info
'''

import sqlite3
import sys
import os
import getopt
import logging
import datetime

def computeSensor(sensor, dayMinus):

	conn = sqlite3.connect(os.path.dirname(os.path.realpath(__file__)) + '/data.db')

	c = conn.cursor()

	#Create missing tables
	c.execute('CREATE TABLE IF NOT EXISTS daily_aggregate(`Sensor` text, `Date` text, AvgTemperature real, AvgHumidity real, MinTempetaure real, MinHumidity real, MaxTemperature real, MaxHumidity real)')

	#Drop data for that day
	c.execute("DELETE FROM daily_aggregate WHERE `Sensor`='"+sensor+"' AND date(`Date`)=(SELECT date('now','-"+str(dayMinus)+" day'))")

	if sensor == "internal":
		tableName = "readouts"
	elif sensor == "external":
		tableName = "readouts_external"
	else:
		logging.error('Unknown sensor ' + sensor)
		sys.exit(2)

	c.execute("INSERT INTO daily_aggregate SELECT '" + sensor + "', date(`Date`), Avg(Temperature), Avg(Humidity), Min(Temperature), Min(Humidity), Max(Temperature), Max(Humidity) FROM " + tableName + " WHERE date(`Date`)=(SELECT date('now','-" + str(dayMinus) + " day'))")

	conn.commit()
	conn.close()

def main(args):
	
	logging.basicConfig(filename=os.path.dirname(os.path.realpath(__file__)) + '/aggregate.log',level=logging.ERROR)

	#logging.basicConfig(filename='aggregate.log',level=logging.DEBUG)

	dayMinus = 1;

	logging.info('Started at ' + datetime.datetime.now().strftime('%Y-%m-%d %H:%M:%S'));

	try:
		options, arguments = getopt.getopt(args, "d:")
	except getopt.GetoptError:
		usage()
		sys.exit(2)

	for arg in options:

		if arg[0] == "-d":
			dayMinus = int(arg[1]);

	computeSensor('internal', dayMinus)
	computeSensor('external', dayMinus)

	logging.info('Finished at ' + datetime.datetime.now().strftime('%Y-%m-%d %H:%M:%S'));

if __name__ == "__main__":
    main(sys.argv[1:])
