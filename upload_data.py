#!/usr/bin/env python
# -*- coding: utf-8 -*- 

import sensor
import subprocess

from openweatherconfig import config

def main():

    sensor_handler = sensor.sensor();

    temperature = sensor_handler.get_last_value(0)
    humidity = sensor_handler.get_last_value(1)
    pressure = sensor_handler.get_last_value(2)

    callScript = "curl -d 'pressure="+str(pressure)+"&humidity="+str(humidity)+"&temp="+str(temperature)+"&"+config['coords']+"' --user '"+config['user']+":"+config['password']+"' http://openweathermap.org/data/post"

    #print callScript

    p = subprocess.Popen(callScript, shell=True, stdout=subprocess.PIPE, stderr=subprocess.STDOUT)	

    # for line in p.stdout.readlines():
        # print line
		
if __name__ == "__main__":
	main()