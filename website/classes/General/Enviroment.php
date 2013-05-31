<?php

namespace General;

/**
 * 
 * Klasa ustawiająca zmienne środowiskowe projektu
 * @author Paweł
 *
 */
class Enviroment extends StaticUtils {
	
	/**
	 * 
	 * Ustawienie zmiennych środowiskowych
	 */
	static public function set() {
	
		header ( 'Content-Type: text/html; charset=utf-8' );
	
		ini_set ( 'date.timezone', 'Europe/Warsaw' );
		ini_set ( 'date.default_latitude', '31.7667' );
		ini_set ( 'date.default_longitude', '35.2333' );
		ini_set ( 'date.sunrise_zenith', '90.583333' );
		ini_set ( 'date.sunset_zenith', '90.583333' );
		ini_set ( 'ibase.timestampformat', '%Y-%m-%d %H:%M:%S' );
		date_default_timezone_set ( "Europe/Warsaw" );
		mb_internal_encoding ( "UTF-8" );
		setlocale(LC_ALL, 'en_US');
	
		\Cache\Memcached::$host = Config::getInstance()->get('memcachedIP');
		\Cache\Memcached::$port = Config::getInstance()->get('memcachedPort');
		\Cache\Apc::sSetPrefix('weather');
		
		\Translate\Controller::setDefaultLanguage('pl');
		
	}
	
}