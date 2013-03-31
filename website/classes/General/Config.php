<?php

namespace General;

class Config implements \ArrayAccess{

	/**
	 * @var Config
	 */
	private static $instance = null;

	/**
	 * @var array
	 */
	protected $config = array();

	public function getAll() {
		return $this->config;
	}

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

	public function offsetSet($offset, $value) {
		$this->config[$offset] = $value;
	}

	public function offsetExists($offset) {
		return isset($this->config[$offset]);
	}

	public function offsetUnset($offset) {
		unset($this->config[$offset]);
	}

	public function offsetGet($offset) {

		if (isset($this->config[$offset])) {
			return $this->config[$offset];
		}else {
			return false;
		}

	}

	public function get($offset) {
		return $this->offsetGet($offset);
	}

	protected function parse() {

		require (dirname ( __FILE__ ) . "/../../config.inc.php");

		$this->config = $config;

	}

	protected function __construct() {
		$this->parse();
	}

	
	
}