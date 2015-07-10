<?php

namespace PhpCache;

/**
 * @deprecated
 */
class Session extends AbstractCache {
	private $currentSize = 0;
	private $timeThreshold = 60;
	private $cacheName = 'cache';
	private $cacheMaintenanceTimeName = 'cacheMaintenanceTime';

    /**
     * Cache maintenance, remove old entries
     * @param CacheKey $key
     * @return bool
     */
    private function maintenance(CacheKey $key) {

		$module = $key->getModule();
		
		if (! isset ( $_SESSION [$this->cacheName] [$module] )) {
			return false;
		}
			
		if (time () < $_SESSION [$this->cacheMaintenanceTimeName] [$module]) {
			return false;
		}
			
		$_SESSION [$this->cacheMaintenanceTimeName] [$module] = time () + $this->timeThreshold;

		$keys = array_keys ( $_SESSION [$this->cacheName] [$module] );

		foreach ( $keys as $value ) {
			if (time() > $_SESSION [$this->cacheName] [$module] [$value] ['time']) {
				unset ( $_SESSION [$this->cacheName] [$module] [$value] );
			}
		}

		return true;
	}

	/**
	 * @return int
	 */
	public function getTimeThreshold() {
		return $this->timeThreshold;
	}

	/**
	 * @param int $timeThreshold
	 */
	public function setTimeThreshold($timeThreshold) {
		$this->timeThreshold = $timeThreshold;
	}

	public function check(CacheKey $key) {
		if (isset ( $_SESSION [$this->cacheName] [$key->getModule()] [$key->getProperty()] )) {
			return true;
		} else {
			return false;
		}
	}

	function get(CacheKey $key) {
		
		$module = $key->getModule();
		$id 	= $key->getProperty();
		
		if (isset ( $_SESSION [$this->cacheName] [$module] [$id] )) {
			$tValue = $_SESSION [$this->cacheName] [$module] [$id] ['value'];
			$this->maintenance($key);
			return $tValue;
		} else {
			return false;
		}
	}

	/**
	 * 
	 * @param CacheKey $key
	 * @param mixed $value
	 * @param int $expire
	 */
	function set(CacheKey $key, $value, $expire = null) {

		$module = $key->getModule();
		$id 	= $key->getProperty();
		
		if ($expire == null) {
			$expire = $this->timeThreshold;
		}
		
		$_SESSION [$this->cacheName] [$module] [$id] ['value'] = $value;
		$_SESSION [$this->cacheName] [$module] [$id] ['time'] = time () + $expire;

		if (! isset ( $_SESSION [$this->cacheMaintenanceTimeName] [$module] )) {
			$_SESSION [$this->cacheMaintenanceTimeName] [$module] = time () + $this->timeThreshold;
		}

		$this->currentSize += 1;
	}

	public function clear(CacheKey $key) {
		
		$module = $key->getModule();
		$id		= $key->getProperty();
		
		if (!empty($id)) {
			unset ( $_SESSION [$this->cacheName] [$module] [$id] );
		} else {
			unset ( $_SESSION [$this->cacheName] [$module] );
		}
	}
	
	public function clearAll() {
		$_SESSION [$this->cacheName] = array();
	}
	
}