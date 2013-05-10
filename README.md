# raspberry_temperature_log

Turn Raspberry Pi into temperature and humidity logging station with DHT11 sensor

![screenshot](/assets/img/1.png)
![raspberry wityh sensor](/assets/img/2.jpg)

## Requirements

* wiringPi
* python
* php5 with SQLite3 enabled
* SQLite3
* any web server: nginx recomended

### wiringPi

* sudo apt-get install git-core
* git clone git://git.drogon.net/wiringPi
* cd wiringPi
* git pull origin
* ./build

C part with wiringPi code is based on http://www.rpiblog.com/2012/11/interfacing-temperature-and-humidity.html 

# external sensor DHT22 on GPIO 17

dht22_sensor readout is based on https://github.com/adafruit/Adafruit-Raspberry-Pi-Python-Code/tree/master/Adafruit_DHT_Driver by Adafruit

to compile You will need BCM2835 C Library:

wget http://www.open.com.au/mikem/bcm2835/bcm2835-1.8.tar.gz
tar -zxvf bcm2835-1.8.tar.gz
cd bcm2835-1.8
./configure
make
sudo make install

do make:

gcc sensor_driver.c -l bcm2835 -std=gnu99 -o sensor_driver
