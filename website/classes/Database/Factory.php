<?php

namespace Database;

/**
 * Fabryka
 * @author Paweł
 *
 */
class Factory {

	private function __construct() {
	}

	/**
	 * @var MySQLiWrapper
	 */
	private static $instance = null;

	/**
	 * Pobranie obiektu bazy danych gameplay
	 * @throws Exception
	 * @return \Database\MySQLiWrapper
	 */
	public static function getInstance() {

		if (empty(self::$instance)) {
			self::connect();
		}

		if (empty(self::$instance)) {
			throw new \Exception('Data Base object failed to initialize');
		}

		return self::$instance;

	}

	/**
	 * Połączenie z bazą danych gameplay
	 */
	private static function connect() {
		self::$instance = new MySQLiWrapper ( Config::getInstance() );

	}

}