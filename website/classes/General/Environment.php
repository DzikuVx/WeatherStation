<?php

namespace General;

use PhpCache\Memcached as Memcached;
use PhpCache\PhpCache;
use Translate\Controller;

class Environment extends StaticUtils {

    static public function setContentHtml() {
        header ( 'Content-Type: text/html; charset=utf-8' );
    }

    static public function setContentJson() {
        header ( 'Content-Type: application/json; charset=utf-8' );
    }

	/**
	 * Set environmental variables
	 */
	static public function set() {
	
		ini_set ( 'date.timezone', 'Europe/Warsaw' );
		ini_set ( 'date.default_latitude', '31.7667' );
		ini_set ( 'date.default_longitude', '35.2333' );
		ini_set ( 'date.sunrise_zenith', '90.583333' );
		ini_set ( 'date.sunset_zenith', '90.583333' );
		date_default_timezone_set ( "Europe/Warsaw" );
		mb_internal_encoding ( "UTF-8" );
		setlocale(LC_ALL, 'en_US');

        /*
         * Set caching configuration
         */
        PhpCache::$sDefaultMechanism = Config::getInstance()->get('cacheMethod');
        Memcached::$host = Config::getInstance()->get('memcachedIP');
        Memcached::$port = Config::getInstance()->get('memcachedPort');

        Controller::setDefaultLanguage('pl');
		
	}
}