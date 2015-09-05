<?php

namespace Proxy;

use Interfaces\Proxy;

class Current extends AbstractProxy implements Proxy {
	protected $sLocalMockup = 'json_mockup/current.json';
	
	public function __construct() {
		$this->sUrl = 'http://api.openweathermap.org/data/2.5/weather?q=' . \General\Config::getInstance()->get('cityName') . '&mode=json&units=metric';
	}
	
}