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

		
	print "Computing average values for today -" + str(dayMinus) + " days"


	conn = sqlite3.connect(os.path.dirname(os.path.realpath(__file__)) + '/data.db')

	c = conn.cursor()

	#Create missing tables
	c.execute('CREATE TABLE IF NOT EXISTS readouts(`Date` text, Temperature real, Humidity real)')


	conn.commit()
	conn.close()

	'''Drop that day


if __name__ == "__main__":
    main(sys.argv[1:])