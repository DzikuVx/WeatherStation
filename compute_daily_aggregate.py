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
		sys.exit(2)

	c.execute("INSERT INTO daily_aggregate SELECT '" + sensor + "', date(`Date`), Avg(Temperature), Avg(Humidity), Min(Temperature), Min(Humidity), Max(Temperature), Max(Humidity) FROM " + tableName + " WHERE date(`Date`)=(SELECT date('now','-" + str(dayMinus) + " day'))")

	conn.commit()
	conn.close()

def main(args):
	print "hello"

	dayMinus = 1;

	'''
	Parse input parameters
	'''
	try:
		options, arguments = getopt.getopt(args, "d:")
	except getopt.GetoptError:
		usage()
		sys.exit(2)

	for arg in options:

		if arg[0] == "-d":
			dayMinus = int(arg[1]);

	print "Computing sensors values for today -" + str(dayMinus) + " days"

	computeSensor('internal', dayMinus)
	computeSensor('external', dayMinus)

if __name__ == "__main__":
    main(sys.argv[1:])