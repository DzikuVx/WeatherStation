# In Development, do not use

# raspberry_temperature_log

Turn Raspberry Pi into temperature and humidity logging station with DHT11 sensor

![screenshot](/assets/img/1.png)
![raspberry wityh sensor](/assets/img/2.jpg)

## Requirements

* wiringPi
* python
* SQLite
* php5
* any web server: nginx recomended

### wiringPi

* sudo apt-get install git-core
* git clone git://git.drogon.net/wiringPi
* cd wiringPi
* git pull origin
* ./build

C part with wiringPi code is based on http://www.rpiblog.com/2012/11/interfacing-temperature-and-humidity.html 
