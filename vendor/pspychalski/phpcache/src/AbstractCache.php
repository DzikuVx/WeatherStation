<?php
namespace PhpCache;

abstract class AbstractCache
{

    static public $sCachePrefix = 'PhpCache';

    /**
     * Set key prefix
     * @param string $prefix
     */
    static public function sSetPrefix($prefix) {
        static::$sCachePrefix = $prefix;
    }

    /**
     * @param CacheKey $key
     * @return string
     */
    protected function getKey(CacheKey $key)
    {
        return static::$sCachePrefix . '__' . $key->getModule() . '||' . $key->getProperty();
    }

    /**
     * @return string
     */
    static public function sGetPrefix() {
        return static::$sCachePrefix;
    }

}