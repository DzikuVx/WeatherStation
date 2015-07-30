<?php

namespace PhpCache;

class Memcached extends AbstractCache {

	private $timeThreshold = 7200;

	private $memcached = null;

	static public $host = '127.0.0.1';
	static public $port = 11211;

	public function __construct() {
		$this->memcached = new \Memcache();
		$this->memcached->connect(self::$host, self::$port);
	}

	public function check(CacheKey $key) {
		
		$tValue = $this->get($key);

		if ($tValue === false) {
			return false;
		}else {
			return true;
		}

	}

	public function get(CacheKey $key) {
		return $this->memcached->get($this->getKey($key));
	}

	/**
	 * Unset cache value
	 * @param CacheKey $key
	 */
	function clear(CacheKey $key) {
		$this->memcached->delete($this->getKey($key));
	}

    /**
     * @param CacheKey $key
     * @depreciated
     */
	public function clearModule(/** @noinspection PhpUnusedParameterInspection */
        CacheKey $key) {
		$this->memcached->flush();
	}
	
	public function set(CacheKey $key, $value, $sessionLength = null) {

		if ($sessionLength == null) {
			$sessionLength = $this->timeThreshold;
		}

		$this->memcached->set($this->getKey($key), $value, null, $sessionLength);
	}

    /**
     * @param null $className
     * @depreciated
     */
	public function clearClassCache(/** @noinspection PhpUnusedParameterInspection */
        $className = null) {
		$this->memcached->flush();
	}

	public function clearAll() {
		$this->memcached->flush();
	}

}