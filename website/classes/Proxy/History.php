<?php

namespace Proxy;

use Interfaces\Proxy;

class History extends AbstractProxy implements Proxy {
	protected $sLocalMockup = 'json_mockup/history.json';

	public function __construct() {
		$this->sUrl = 'http://api.openweathermap.org/data/2.5/history/city?id=' . \General\Config::getInstance()->get('cityId') . '&type=hour&cnt=80&units=metric';
	}
	
}