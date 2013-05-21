# raspberry_temperature_log

Turn Raspberry Pi into temperature and humidity logging station with DHT11 sensor

![screenshot](/assets/img/3.png)
![raspberry with sensor](/assets/img/2.jpg)
![raspberry with sensor](/assets/img/4.jpg)

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

* Do electrical stuff like showed on diagram. It's really simple
* clone this repository `git clone https://github.com/DzikuVx/raspberry_temperature_log.git`
* `cd raspberry_temperature_log`
* build sensor driver `sh build_sensor.sh`
* check if sensors are working `python get_data.py`
* add following line to cron (with `crontab -e`), it will get save data do database every 10 minutes: `*/10 * * * * sudo python /home/pi/raspberry_temperature_log/get_data.py`
* configure Raspberry Pi web server, example configuration for nginx, PHP5-FMP and domain http://temperature.spychalski.info included below
* that's all

## Example nginx configuration

```
server {
  listen 0.0.0.0:80;

  server_name temperature.spychalski.info;

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

# external sensor DHT22 on GPIO 17

dht22_sensor readout is based on https://github.com/adafruit/Adafruit-Raspberry-Pi-Python-Code/tree/master/Adafruit_DHT_Driver by Adafruit

to make:

gcc sensor_driver.c -l bcm2835 -std=gnu99 -o sensor_driver
