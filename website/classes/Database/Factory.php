<?php

namespace Database;

/**
 * @author Paweł
 *
 */
class Factory {

	private function __construct() {
	}

	/**
	 * @var SQLiteWrapper
	 */
	private static $instance = null;

    /**
     * @throws \Exception
     * @return \Database\SQLiteWrapper
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

	private static function connect() {
		self::$instance = new SQLiteWrapper ( Config::getInstance() );

	}

}