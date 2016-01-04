#!/usr/bin/env python
# -*- coding: utf-8 -*- 

import urllib
import urllib2

from thingspeakconfig import config

import sensor

def main():

    sensor_handler = sensor.sensor();

    temperature = sensor_handler.get_last_value(0)
    humidity = sensor_handler.get_last_value(1)
    pressure = sensor_handler.get_last_value(2)
     
    encodedAttributes = {
        'key': config['api'],
        'field1': temperature,
        'field2': humidity,
        'field3': pressure
    }
    req = urllib2.Request(config['url'] + "?" + urllib.urlencode(encodedAttributes))
    response=urllib2.urlopen(req)
    print "Posted!"

if __name__ == "__main__":
    main()