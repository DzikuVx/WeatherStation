# WeatherStation 1.2.0

Turn Raspberry Pi into weather station with DHT22 sensor and OpenWeatherMap.org

Live example located in Szczecin, Poland can be found here: http://weather.spychalski.info/

Blog: http://shtr.eu/

![screenshot](/assets/img/screen1.png)

# Electrical diagram

![diagram](/assets/img/WeatherStation_schem.png)

## Requirements

* Raspberry Pi
* pigpio library installed
* DHT22 temperature and humidity sensor
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
sudo python setup.py install
```
* install [pigpio library](http://abyz.co.uk/rpi/pigpio/)
```
wget abyz.co.uk/rpi/pigpio/pigpio.zip
unzip pigpio.zip
cd PIGPIO
make
make install
```
* pigpio has to be running as a service, so add `sudo /home/pi/PIGPIO/pigpiod` to `/etc/rc.local`
* check if you have SQLite3 PHP library. If not, or not sure, install it `sudo apt-get install php5-sqlite`
* check if you have nginx+php. If not, or not sure, install it `sudo apt-get install nginx php5-fpm php-apc`
* clone this repository `git clone git@github.com:DzikuVx/WeatherStation.git`
* `cd WeatherStation`
* get all submodules while inside repository root folder:
** `git submodule init`
** `git submodule update`
* check if sensors are working `python get_data.py`
* configure Raspberry Pi web server, example configuration for nginx, PHP5-FMP and domain http://weather.spychalski.info included below
* that's all

# Configuration

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

# Migration from previous versions

Starting from release 1.2.0 WeatherStation starts to use new database with different tables and field names. If you have previous version running and want to update to 1.2.0 or newer you have to run migration scrit.

To run migration do as follows:
* make a copy of `data.db`
* update WeatherStation to new version
* make sure that `data.db` was not overwiritten during update. If so, restore it if from a copy mada in step 1
* run migration script `python migrate.py`
* scripts requires few minutes to execute. When it finishes, compare sizes of `data.db` and `data-new.db`. They should be similar in size (up to 20% difference is acceptable)

**If you are doing a fresh install, migration is not required**

# Crontab

To serve its purpose, WeatherStation need to collect data on regular basis. That's why you need to configure some crontab jobs (`crontab -e`):

* Collect data from sensor `*/20 * * * * sudo python /home/pi/WeatherStation/get_data.py`
* Collect from OpenWeatherMap.org (current pressure and wind) `*/20 * * * * python /home/pi/WeatherStation/get_external_data.py`
* Upload data to OpenWeatherMap.org (because we like to share, don't we?) `*/30 * * * * python /home/pi/WeatherStation/upload_data.py`
* For best webpage performance, prefetch forecast and history `*/15 * * * * wget -qO- http://weather.spychalski.info/cron.php &> /dev/null`

# Example nginx configuration

```
server {
  listen 0.0.0.0:80;

  server_name weather.spychalski.info;

  access_log off;

  root /home/pi/WeatherStation/website;
  index index.php;

  # default try order
  location / {
    try_files $uri $uri/ /index.php?$args;
    add_header Access-Control-Allow-Origin *;
  }

  # enable php
  location ~ \.php$ {
    fastcgi_pass unix:/var/run/php5-fpm.sock;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    include fastcgi_params;
  }
}

```

# Screenhoths
## Forecast
![screenshot](/assets/img/screen2.png)
## Monthly history
![screenshot](/assets/img/screen3.png)
## Sensor
![raspberry with sensor](/assets/img/2.jpg)
## Sensor
![raspberry with sensor](/assets/img/4.jpeg)

#Internet Of Things - ThingSpeak integration

Weather station provides simple [ThingSpeak](https://thingspeak.com/) integration using REST api. This allows to upload collected measurements to ThingSpeak.

To enable ThingSpeak data upload:

* [Create TS account](https://thingspeak.com/users/sign_up)
* [Create TS Channel](https://thingspeak.com/channels/new)
* Enable 3 fields for this channel and name them:
** temperature
** humidity
** pressure
* Copy Write API key and paste it to `thingspeakconfig.py`
* To upload data to ThingSpeak run `python thingspeak_post.py`
* You can automate whole process by adding it to crontab `*/30 * * * * python /home/pi/WeatherStation/thingspeak_post.py`

# Development

To develop web interface:

`docker-compose up -d`

```cd website
./node_modules/gulp/bin/gulp.js```
