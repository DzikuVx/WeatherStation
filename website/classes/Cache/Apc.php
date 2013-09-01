<?php

namespace Cache;

/**
 * Wrapper realizujący cache współdzielony za pomocą APC
 * @author Paweł Spychalski 2013
 * @version 2.0.1 Alfa
 */
class Apc implements \Interfaces\Singleton  {

	/**
	 * Prefix nazw klucza
	 * @var string
	 */
	static private $sCachePrefix = 'apc';

	/**
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
	 * @param CacheKey $key
	 * @return boolean
	 */
	public function check(CacheKey $key) {

		$tValue = $this->get($key);
			
		if ($tValue === false) {
			return false;
		}else {
			return true;
		}
	}

	/**
	 * Pobranie wartości z cache
	 * @param CacheKey $key
	 * @return mixed
	 */
	public function get(CacheKey $key) {

		$retVal = apc_fetch($this->getKey($key));

		return $retVal;
	}

	/**
	 * Wyczyszczenie konkretnego wpisu w cache
	 *
	 * @param CacheKey $key
	 */
	public function clear(CacheKey $key) {
		apc_delete($this->getKey($key));
	}

	/**
	 * Wyczyszczenie konkretnego modułu cache
	 *
	 * @param CacheKey $key
	 */
	public function clearModule(CacheKey $key) {

		$module = $key->getModule();
		
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
	 * @param CacheKey $key
	 * @param mixed $value
	 * @param int $sessionLength
	 */
	public function set(CacheKey $key, $value, $sessionLength = null) {

		if ($sessionLength == null) {
			$sessionLength = $this->timeThreshold;
		}

		apc_store ( $this->getKey($key) , $value , $sessionLength);
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
	* @param CacheKey $key
	* @return string
	*/
	private function getKey(CacheKey $key) {
		return self::$sCachePrefix.'__'.$key->getModule().'||'.$key->getProperty();
	}
}