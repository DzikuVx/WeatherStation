<?php

namespace PhpCache;

use Predis\Client;

class Redis extends AbstractCache {

    /**
     * Default cache validity time [s]
     *
     * @var int
     */
    private $timeThreshold = 7200;

    /**
     * Check if cache entry exist
     * @param CacheKey $key
     * @return boolean
     */
    public function check(CacheKey $key) {
        return $this->redis->exists($this->getKey($key));
    }

    /**
     * @var Client
     */
    private $redis;

    static public $host = '127.0.0.1';
    static public $port = 6379;
    static public $db = 0;

    public function __construct() {
        $this->redis = new Client(array(
            'host' => self::$host,
            'port' => self::$port,
            'database' => self::$port
        ));

        $this->redis->select(self::$db);
    }

    /**
     * Get cache value
     * @param CacheKey $key
     * @return mixed
     */
    public function get(CacheKey $key) {

        $value = $this->redis->get($this->getKey($key));

        $unserialized = @unserialize($value);

        if ($unserialized !== false) {
            $value = $unserialized;
        } else if ($value === null) {
            $value = false;
        }

        return $value;
    }

    /**
     * Unset cache value
     * @param CacheKey $key
     */
    public function clear(CacheKey $key) {
        $this->redis->del($this->getKey($key));
    }

    /**
     * @param CacheKey $key
     * @depreciated
     */
    public function clearModule(/** @noinspection PhpUnusedParameterInspection */
        CacheKey $key) {
        $this->redis->flushdb();
    }

    /**
     * Set cache value
     *
     * @param CacheKey $key
     * @param mixed $value
     * @param int $sessionLength
     */
    public function set(CacheKey $key, $value, $sessionLength = null) {

        if ($sessionLength == null) {
            $sessionLength = $this->timeThreshold;
        }

        $sKey = $this->getKey($key);

        if (is_array($value) || is_object($value)) {
            $value = serialize($value);
        }

        $this->redis->set($sKey, $value);

        if (!empty($sessionLength) && $sessionLength > 0) {
            $this->redis->expire($sKey, $sessionLength);
        }
    }

    /**
     * @param string $className
     * @depreciated
     */
    public function clearClassCache($className = null) {
        $this->redis->flushdb();
    }

    public function clearAll() {
        $this->redis->flushdb();
    }

}