# WeatherStation

Turn Raspberry Pi into weather station with DHT22 sensor and OpenWeatherMap.org

![screenshot](/assets/img/screen1.png)

#Electrical diagram

![diagram](/assets/img/WeatherStation_schem.png)

## Requirements

* BCM2835 C Library
* BMP180 (or BMP085) I2C pressure sensor
* python
* php5 with SQLite3 enabled
* SQLite3
* I2C enabled Raspberry Pi Raspbian distribution
* any web server: nginx recomended

# Installation

* Do electrical stuff like showed on diagram. It's really simple
* Enable I2C in your Raspberry Pi. You can use example from [instructables](http://www.instructables.com/id/Raspberry-Pi-I2C-Python/)
* Install [Adafruit BMP Python library](https://learn.adafruit.com/using-the-bmp085-with-raspberry-pi/using-the-adafruit-bmp-python-library)
```
sudo apt-get update
sudo apt-get install git build-essential python-dev python-smbus
git clone https://github.com/adafruit/Adafruit_Python_BMP.git
cd Adafruit_Python_BMP
sudo python setup.py install```
* check if you have SQLite3 PHP library. If not, or not sure, install it `sudo apt-get install php5-sqlite3`
* check if you have BCM2835 C Library installed. If not, setup instruction is in the next paragraph 
* clone this repository `git clone git@github.com:DzikuVx/WeatherStation.git`
* `cd WeatherStation`
* get all submodules while inside repository root folder:
** `git submodule init`
** `git submodule update`
* build sensor driver `sh build_sensor.sh`
* check if sensors are working `python get_data.py`
* configure Raspberry Pi web server, example configuration for nginx, PHP5-FMP and domain http://weather.spychalski.info included below
* that's all

#Configuration

To configure WeatherStation to work with OpenWeatherMap you need to set some data in a few places.

For example, to get data for Szczecin, Poland you need to:
* register at http://openweathermap.org/
* get your API key
* edit website/config.inc.php and set `$config['cityId'] = 3083829;`. Of course, 3083829 is city ID for Szczecin, Poland. For any other city you need to set other ID (but this is obvious). 
* replace openweatherconfig.py template with
```
config = {
    'user': 'DzikuVx',
    'password': 'you_would_like_to_know',
    'api': 'yes_you_do_want_to_have_own_api_key',
    'location': 'Szczecin,PL',
    'coords': 'lat=53.48&long=14.40&alt=100'
}
```

#Crontab

To serve its purpose, WeatherStation need to collect data on regular basis. That's why you need to configure some crontab jobs (`crontab -e`):

* Collect data from sensor `*/20 * * * * sudo python /home/pi/WeatherStation/get_data.py`
* Collect from OpenWeatherMap.org (current pressure and wind) `*/20 * * * * python /home/pi/WeatherStation/get_external_data.py`
* Upload data to OpenWeatherMap.org (because we like to share, don't we?) `*/30 * * * * python /home/pi/WeatherStation/upload_data.py`
* For best webpage performance, prefetch forecast and history `*/30 * * * * wget http://weather.spychalski.info/cron.php`

# BCM2835 C Library installation

* `wget http://www.open.com.au/mikem/bcm2835/bcm2835-1.8.tar.gz`
* `tar -zxvf bcm2835-1.8.tar.gz`
* `cd bcm2835-1.8`
* `./configure`
* `make`
* `sudo make install`

# Example nginx configuration

```
server {
  listen 0.0.0.0:80;

  server_name weather.spychalski.info;

  access_log off;

  root /home/pi/raspberry_temperature_log/website;
  index index.php;

  # default try order
  location / {
    try_files $uri $uri/ /index.php?$args;
  }

  # enable php
  location ~ \.php$ {
    fastcgi_pass unix:/var/run/php5-fpm.sock;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    include fastcgi_params;
  }
}

```

# Legal stuff

sensor_driver.c readout is based on https://github.com/adafruit/Adafruit-Raspberry-Pi-Python-Code/tree/master/Adafruit_DHT_Driver by Adafruite

#Screenhoths
## Forecast
![screenshot](/assets/img/screen2.png)
## Monthly history
![screenshot](/assets/img/screen3.png)
##Sensor
![raspberry with sensor](/assets/img/2.jpg)
##Sensor
![raspberry with sensor](/assets/img/4.jpeg)