<?php

namespace Proxy;

use Interfaces\Proxy;

class Forecast extends AbstractProxy implements Proxy {
	protected $sLocalMockup = 'json_mockup/forecast.json';
	
	public function __construct() {
		$this->sUrl = 'http://api.openweathermap.org/data/2.5/forecast/daily?id=' . \General\Config::getInstance()->get('cityId') . '&cnt=9&mode=json&units=metric';
	}
	
}