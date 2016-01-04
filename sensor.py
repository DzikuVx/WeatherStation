#!/usr/bin/env python
# -*- coding: utf-8 -*-

__author__ = 'pspychalski'

import sqlite3
import os

class sensor():
    
    def __init__(self):
        self.conn = sqlite3.connect(os.path.dirname(os.path.realpath(__file__)) + '/data-new.db')
    
    def get_last_value(self, sensor):
        cur = self.conn.cursor()
        cur.execute('SELECT Value FROM sensor_values WHERE Sensor=' + str(sensor) + ' ORDER BY `Date` DESC LIMIT 1')

        data = cur.fetchone()

        if data == None:
            return None
        else:
            return data[0]
            
        