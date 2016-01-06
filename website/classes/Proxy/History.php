<?php

namespace Proxy;

use General\Config;
use Interfaces\Proxy;

/**
 * Class History
 * @package Proxy
 * @deprecated
 * FIXME History API on openweathermap is not working for some cities
 */
class History extends AbstractProxy implements Proxy {
	protected $sLocalMockup = 'json_mockup/history.json';

	public function __construct() {
		$this->sUrl = 'http://api.openweathermap.org/data/2.5/history/city?id=' . Config::getInstance()->get('cityId') . '&type=hour&units=metric&start=' . (time() - (14 * 86400)) . '&end=' . time();
    }
	
}