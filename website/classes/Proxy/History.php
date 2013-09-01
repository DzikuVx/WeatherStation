<?php

namespace Proxy;

use Interfaces\Proxy;

class History extends AbstractProxy implements Proxy {
	
	protected $sUrl = 'http://api.openweathermap.org/data/2.5/history/city?id=3083829&type=hour&cnt=80&units=metric';
	
	protected $sLocalMockup = 'json_mockup/history.json';
	
}