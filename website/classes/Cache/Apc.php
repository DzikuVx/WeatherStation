<?php

namespace Cache;

/**
 * Wrapper realizujący cache współdzielony za pomocą APC
 * @author Paweł Spychalski 2011
 */
class Apc implements \Interfaces\Singleton  {

	/**
	 * Prefix nazw klucza
	 * @var string
	 */
	static private $sCachePrefix = 'JST';

	/**
	 * Enter description here ...
	 * @var int
	 */
	static private $gcTimeThreshold = 30;

	/**
	 * Typ Garbage Collectora
	 * @var string
	 * access_time
	 * mtime
	 * creation_time
	 */
	private static $gcMethod = 'access_time';

	/**
	 * Obiekt klasy -> Singleton
	 * @var \Cache\Apc
	 */
	private static $instance;

	/**
	 * Wewnętrzny cache klasy
	 * @var array
	 */
	private $internalCache = Array();

	/**
	 * Konstruktor statyczny
	 * @return \Cache\Apc
	 */
	public static function getInstance(){
		if (empty(self::$instance)) {
			$className = __CLASS__;
			self::$instance = new $className;
		}
		return self::$instance;
	}

	/**
	 * Ustawienie prefixu na nazwy klucza
	 * @param string $prefix
	 */
	static public function sSetPrefix($prefix) {
		self::$sCachePrefix = $prefix;
	}

	/**
	 * Konstruktor prywatny
	 */
	private function __construct() {
		if (time() - $this->getGcRunTime() > self::$gcTimeThreshold) {
			$this->setGcRunTime();
			$this->garbageCollector();
		}
	}

	/**
	 * Poprawnie prefixu nazw klucza
	 * @return string
	 */
	static public function sGetPrefix() {
		return self::$sCachePrefix;
	}

	/**
	 * Domyślny czas ważności cache [s]
	 *
	 * @var int
	 */
	private $timeThreshold = 7200;

	/**
	 * Sprawdzenie, czy w cache znajduje się wpis
	 * @param string $module
	 * @param string $property
	 * @return boolean
	 */
	public function check($module, $property) {

		$tValue = $this->get($module, $property);

		if ($tValue === false) {
			return false;
		}else {
			return true;
		}
	}

	/**
	 * Pobranie wartości z cache
	 * @param string $module
	 * @param string $property
	 * @return mixed
	 */
	public function get($module, $property) {

		$key = $this->getKey($module, $property);

		if (isset($this->internalCache[$key])) {
			$retVal = $this->internalCache[$key];
		}else {
			$retVal = apc_fetch($key);
			$this->internalCache[$key] = $retVal;
		}

		return $retVal;
	}

	/**
	 * Wyczyszczenie konkretnego wpisu w cache
	 *
	 * @param string $module
	 * @param string $property
	 */
	public function clear($module, $property = null) {
		apc_delete($this->getKey($module, $property));
	}

	/**
	 * Wyczyszczenie konkretnego modułu cache
	 *
	 * @param string $module
	 */
	public function clearModule($module = null) {

		$iterator = new \APCIterator('user');
		while ($iterator->current()) {

			$tKey = $iterator->key();

			if (mb_strpos ( $tKey, $module . '||' ) !== false) {
				apc_delete($tKey);
			}
			$iterator->next();
		}

	}

	/**
	 * Wstawienei do cache
	 *
	 * @param string $module
	 * @param string $property
	 * @param mixed $value
	 * @param int $sessionLength
	 */
	public function set($module, $property, $value, $sessionLength = null) {

		if ($sessionLength == null) {
			$sessionLength = $this->timeThreshold;
		}

		apc_store ( $this->getKey($module, $property) , $value , $sessionLength);
	}

	private function getGcRunTime() {

		$retVal = apc_fetch('CacheOverApcGcRunTime');

		if ($retVal === false) {
			$retVal = 0;
		}

		return $retVal;
	}

	private function setGcRunTime() {
		@apc_store ( 'CacheOverApcGcRunTime' , time() , 86400);
	}

	public function runGarbageCollector() {
		$this->garbageCollector();
	}

	/**
	 * Garbage collector niszczący wpisy o dacie starszej niż zakładany okres
	 */
	private function garbageCollector() {
		$iterator = new \APCIterator('user');
		while ($tKey = $iterator->current()) {

			if (time() - $tKey['ttl'] > $tKey[self::$gcMethod]) {
				apc_delete($tKey['key']);
			}

			$iterator->next();
		}
	}

	/**
	 * Wyczyszczenie wpisów zależnych od podanej klasy
	 *
	 * @param string $className
	 */
	public function clearClassCache($className = null) {

		$iterator = new \APCIterator('user');
		while ($iterator->current()) {

			$tKey = $iterator->key();

			if (mb_strpos ( $tKey, $className . '::' ) !== false) {
				apc_delete($tKey);
			}
			$iterator->next();
		}

	}

	/**
	 * 
	 * Flush opcode cache
	 * @since 2012-08-01
	 */
	public function flushOpcode() {
		apc_clear_cache('opcode');
	}
	
	/**
	 * Oczyszczenie całego cache
	 */
	public function clearAll() {
		apc_clear_cache('user');
	}

	/**
	* Zestawienie klucza
	* @param string $module
	* @param string $property
	* @return string
	*/
	private function getKey($module, $property) {
		return self::$sCachePrefix.'__'.$module.'||'.$property;
	}
}