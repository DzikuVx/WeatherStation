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
import logging
import datetime
import sensor

db_connection = None

def saveSQLite(data):
    conn = sqlite3.connect(os.path.dirname(os.path.realpath(__file__)) + '/data.db')

    c = conn.cursor()
    c.execute('CREATE TABLE IF NOT EXISTS readouts_external(`Date` text, Temperature real, Humidity real)')
    c.execute("INSERT INTO readouts_external(`Date`, Humidity, Temperature) VALUES(datetime('now','localtime'), " + str(
        data[0]) + "," + str(data[1]) + ")")

    conn.commit()
    conn.close()

def main():

    sensor_handler = sensor.sensor();

    FORMAT = '%(asctime)-15s %(message)s'
    logging.basicConfig(filename=os.path.dirname(os.path.realpath(__file__)) + '/dht22.log', level=logging.DEBUG,
                        format=FORMAT)
    logger = logging.getLogger('dht22')

    print "DHT22 Sensor:"

    readout = None

    counter = 0

    try:
        pi = pigpio.pi()
    except ValueError:
        print "Failed to connect to PIGPIO (%s)"
        logger.error('Failed to connect to PIGPIO (%s)', ValueError);

    try:
        sensor_data = DHT22.sensor(pi, 17)
    except ValueError:
        print "Failed to connect to DHT22"
        logger.error('Failed to connect to DHT22 (%s)', ValueError);

    while (readout == None and counter < 5):

        counter += 1

        # Get data from sensor
        sensor_data.trigger()
        time.sleep(0.2)

        humidity = sensor_data.humidity()
        temperature = sensor_data.temperature()

        if humidity != None and temperature != None and humidity >= 0 and humidity <= 100:

            readout = [humidity, temperature]

            saveSQLite(readout)

            sensor_handler.save_value(0, temperature)
            sensor_handler.save_value(1, humidity)

            print "Humidity: " + str(humidity)
            print "Temperature: " + str(temperature)
            counter = 10
        else:
            time.sleep(5)


if __name__ == "__main__":
    main()
