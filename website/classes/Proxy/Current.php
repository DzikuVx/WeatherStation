<?php

namespace Proxy;

use Interfaces\Proxy;

class Current extends AbstractProxy implements Proxy {
	protected $sLocalMockup = 'json_mockup/current.json';
	
	public function __construct() {
		$this->sUrl = 'http://api.openweathermap.org/data/2.5/weather?id=' . \General\Config::getInstance()->get('cityId') . '&mode=json&units=metric';
	}
	
}