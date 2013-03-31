<?php

namespace Cache;

/**
 * Wrapper realizujący cache współdzielony za pomocą memcached
 * @author Paweł Spychalski 2011
 * @see php memchache
 */

class Memcached{

	/**
	 * Domyślny czas ważności cache [s]
	 *
	 * @var int
	 */
	private $timeThreshold = 7200;

	/**
	 * Czy dokonuwać kompresji pliku cache
	 *
	 * @var boolean
	 */
	private $useZip = false;

	/**
	 * Obiekt memcached
	 * @var Memcache
	 */
	private $memcached = null;

	/**
	 * Obiekt klasy -> Singleton
	 */
	private static $instance;

	/**
	 * Konstruktor statyczny
	 */
	public static function getInstance(){
		if (empty(self::$instance)) {
			$className = __CLASS__;
			self::$instance = new $className;
		}
		return self::$instance;
	}

	/**
	 * Cache wewnętrzny do współpracy check-get
	 * @var array
	 */
	private $internalCache = array();

	static public $host = '192.168.2.99';
	static public $port = 11211;

	/**
	 * Konstruktor
	 */
	private function __construct() {
		$this->memcached = new \Memcache();
		$this->memcached->connect(self::$host, self::$port);
	}

	/**
	 * Sprawdzenie, czy w cache znajduje się wpis
	 *
	 * @param string $module
	 * @param string $property
	 * @return boolean
	 */
	function check($module, $property) {

		$tValue = $this->get($module, $property);

		if ($tValue === false) {
			return false;
		}else {
			return true;
		}

	}

	/**
	 * Pobranie wartości z cache
	 *
	 * @param string $module
	 * @param string $property
	 * @return mixed
	 */
	function get($module, $property) {

		$tKey = $this->getKey($module, $property);

		if (!isset($this->internalCache[$tKey])) {
			$this->internalCache[$tKey] = $this->memcached->get($tKey);
		}

		return $this->internalCache[$tKey];
	}

	/**
	 * Wyczyszczenie konkretnego wpisu w cache
	 *
	 * @param string $module
	 * @param string $property
	 */
	function clear($module, $property = null) {
		$this->memcached->delete($this->getKey($module, $property));
	}

	/**
	 * Wyczyszczenie konkretnego modułu cache
	 *
	 * @param string $module
	 */
	function clearModule($module = null) {

		$this->memcached->flush();

	}

	/**
	 * Wstawienei do cache
	 *
	 * @param string $module
	 * @param string $property
	 * @param mixed $value
	 * @param int $sessionLength
	 */
	function set($module, $property, $value, $sessionLength = null) {

		if ($sessionLength == null) {
			$sessionLength = $this->timeThreshold;
		}

		$this->memcached->set($this->getKey($module, $property), $value, $this->useZip, $sessionLength);
	}

	/**
	 * Wyczyszczenie wpisów zależnych od podanej klasy
	 *
	 * @param string $className
	 */
	public function clearClassCache($className = null) {

		$this->memcached->flush();
	}

	/**
	 * Oczyszczenie całego cache
	 */
	public function clearAll() {
		$this->memcached->flush();
	}

	/**
	 * Zestawienie klucza
	 * @param string $module
	 * @param string $property
	 * @return string
	 */
	private function getKey($module, $property) {
		return $module.'::'.$property;
	}

	/**
	 * Pobranie statystyk memcached
	 * @return array
	 */
	public function getStatistics() {
		return $this->memcached->getStats();
	}

}