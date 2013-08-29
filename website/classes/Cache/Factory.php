<?php

namespace Cache;

/**
 * Kontroler cache
 * Static
 * @author Paweł
 *
 */
class Factory implements \Interfaces\Singleton {

	/**
	 * Obiekt klasy cache
	 * @var mixed
	 */
	private static $cacheInstance;

	/**
	 * Konstruktor prywatny
	 */
	private function __construct() {

	}

	/*
	 * Metoda tworząca obiekt cache
	 */
	static private function create() {

		
		if (empty(self::$cacheInstance)) {

			$sCachingMethod = \General\Config::getInstance()->get('cacheMethod');

			if ($sCachingMethod === 'apc' && !(extension_loaded('apc') && ini_get('apc.enabled'))) {
				$sCachingMethod = 'Mem';
			}
			
			switch ($sCachingMethod) {

				case 'Apc':
					self::$cacheInstance = Apc::getInstance();
					break;

				case 'Memcached':
					self::$cacheInstance = Memcached::getInstance();
					break;
				
				default:
					self::$cacheInstance = Variable::getInstance();
					break;
				
			}
			
		}

	}

	/**
	 * Pobranie obiektu klasy cacheującej
	 * @throws Exception
	 * @return Memcached
	 */
	static public function getInstance() {

		if (empty(self::$cacheInstance)) {
			self::create();
		}

		if (empty(self::$cacheInstance)) {
			throw new \Exception('Cache object is not initialized');
		}

		return self::$cacheInstance;
	}

}