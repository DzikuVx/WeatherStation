# raspberry_temperature_log

Turn Raspberry Pi into temperature and humidity logging station with DHT11 sensor

## Requirements

* wiringPi
* python
* mysql-server: sudo apt-get install mysql-server
* python mysql: sudo apt-get install python-mysqldb
* php5
* any web server: nginx recomended

## MySQL database

Project uses MySQL database to store temp/humidity readout
Database structure avaible in sqldump.sql

To configure, edit log_data and enter correct host, user, password and db name

### wiringPi

* sudo apt-get install git-core
* git clone git://git.drogon.net/wiringPi
* cd wiringPi
* git pull origin
* ./build

C part with wiringPi code is based on http://www.rpiblog.com/2012/11/interfacing-temperature-and-humidity.html 
