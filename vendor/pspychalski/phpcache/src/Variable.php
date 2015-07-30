<?php

namespace PhpCache;

/**
 * Entries are stored in an PHP variable. They are not available between requests
 */
class Variable extends AbstractCache  {

	/**
	 * @var array
	 */
	private $cache = array ();

	public function clearAll() {
	}

	/**
	 * Set cache value
	 *
	 * @param CacheKey $key
	 * @param mixed $value
	 * @param int $sessionLength
     * @return bool
	 */
	public function set(CacheKey $key, $value, /** @noinspection PhpUnusedParameterInspection */
                        $sessionLength = null) {

		$this->cache [$key->getModule()] [$key->getProperty()] = $value;

		return true;
	}

	/**
	 * Check if cache entry exist
	 * @param CacheKey $key
	 * @return boolean
	 */
	public function check(CacheKey $key) {

		if (isset ($this->cache [$key->getModule()] [$key->getProperty()])) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Get cache value
	 * @param CacheKey $key
	 * @return mixed
	 */
	public function get(CacheKey $key) {

		if (isset ( $this->cache [$key->getModule()] [$key->getProperty()] )) {
			return $this->cache [$key->getModule()] [$key->getProperty()];
		} else {
			return false;
		}
	}

	/**
	 * Unset cache value
	 * @param CacheKey $key
	 */
	public function clear(CacheKey $key) {
		unset($this->cache [$key->getModule()] [$key->getProperty()]);
	}

}
