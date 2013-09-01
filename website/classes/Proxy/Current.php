<?php

namespace Proxy;

use Interfaces\Proxy;

class Current extends AbstractProxy implements Proxy {
	
	protected $sUrl = 'http://api.openweathermap.org/data/2.5/weather?id=3083829&mode=json&units=metric';
	protected $sLocalMockup = 'json_mockup/current.json';
	
}