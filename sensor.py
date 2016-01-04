#!/usr/bin/env python
# -*- coding: utf-8 -*-

__author__ = 'pspychalski'

import sqlite3
import os

class sensor():
    
    def __init__(self):
        self.conn = sqlite3.connect(os.path.dirname(os.path.realpath(__file__)) + '/data-new.db')
    
    def get_db_connection(self):
        return self.conn
    
    def get_last_value(self, sensor):
        cur = self.conn.cursor()
        cur.execute('SELECT Value FROM sensor_values WHERE Sensor=' + str(sensor) + ' ORDER BY `Date` DESC LIMIT 1')

        data = cur.fetchone()

        cur.close()

        if data == None:
            return None
        else:
            return data[0]

    def insert_no_commit(self, date, sensor, value):
        c = self.conn.cursor()
        c.execute("INSERT INTO sensor_values(`Date`, `Sensor`, `Value`) VALUES(strftime('%s', '" + str(date) + "','localtime'), " + str(
        sensor) + "," + str(value) + ")")
        c.close()
            
    def save_value(sensor, value):

        c = self.conn.cursor()
        c.execute('CREATE TABLE IF NOT EXISTS sensor_values(`Date` integer, `Sensor` integer, `Value` real)')
        c.execute('CREATE INDEX IF NOT EXISTS SENSOR_A ON sensor_values(`Date`, `Sensor`)')

        c.execute("INSERT INTO sensor_values(`Date`, `Sensor`, `Value`) VALUES(strftime('%s', 'now', 'localtime'), " + str(
            sensor) + "," + str(value) + ")")

        self.conn.commit()
        c.close()
        