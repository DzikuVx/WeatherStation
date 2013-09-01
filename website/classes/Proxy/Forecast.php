<?php

namespace Proxy;

use Interfaces\Proxy;

class Forecast extends AbstractProxy implements Proxy {
	
	protected $sUrl = 'http://api.openweathermap.org/data/2.5/forecast/daily?id=3083829&cnt=9&mode=json&units=metric';
	protected $sLocalMockup = 'json_mockup/forecast.json';
	
}