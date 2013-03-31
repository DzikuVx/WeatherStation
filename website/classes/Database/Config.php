<?php

namespace Database;

class Config extends \General\Config implements \ArrayAccess{

	/**
	 * @var Config
	 */
	private static $instance = null;

	/**
	 * Konstruktor statyczny -> Singleton
	 * @return Config
	 */
	public static function getInstance() {

		if (empty(self::$instance)) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	protected function parse() {

		require (dirname ( __FILE__ ) . "/../../db.inc.php");

		$this->config = $config;

	}

}