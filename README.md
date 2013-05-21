# raspberry_temperature_log

Turn Raspberry Pi into temperature and humidity logging station with DHT11 sensor

![screenshot](/assets/img/1.png)
![raspberry wityh sensor](/assets/img/2.jpg)

#Electrical diagram

![diagram](diagram.png)

## Requirements

* BCM2835 C Library
* python
* php5 with SQLite3 enabled
* SQLite3
* any web server: nginx recomended

# BCM2835 C Library installation

* `wget http://www.open.com.au/mikem/bcm2835/bcm2835-1.8.tar.gz`
* `tar -zxvf bcm2835-1.8.tar.gz`
* `cd bcm2835-1.8`
* `./configure`
* `make`
* `sudo make install`

# Installation

# external sensor DHT22 on GPIO 17

dht22_sensor readout is based on https://github.com/adafruit/Adafruit-Raspberry-Pi-Python-Code/tree/master/Adafruit_DHT_Driver by Adafruit

to make:

gcc sensor_driver.c -l bcm2835 -std=gnu99 -o sensor_driver
