<?php

namespace General;

use \phpCache\Memcached as Memcached;
use \phpCache\Apc as Apc;

/**
 * 
 * Klasa ustawiająca zmienne środowiskowe projektu
 * @author Paweł
 *
 */
class Environment extends StaticUtils {
	
	/**
	 * Set environmental variables
	 */
	static public function set() {
	
		header ( 'Content-Type: text/html; charset=utf-8' );
	
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
        \phpCache\Factory::$sDefaultMechanism = \General\Config::getInstance()->get('cacheMethod');
        Memcached::$host = Config::getInstance()->get('memcachedIP');
        Memcached::$port = Config::getInstance()->get('memcachedPort');
        Apc::sSetPrefix('weather');

        \Translate\Controller::setDefaultLanguage('pl');
		
	}
}