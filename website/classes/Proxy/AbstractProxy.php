<?php
namespace Proxy;

use General\Config;
use psDebug\Debug;

use Interfaces\Proxy;
use PhpCache\CacheKey;
use PhpCache\PhpCache;

abstract class AbstractProxy implements Proxy{
	
	/**
	* @var int
	 */
	protected $cacheTime = 3600;
	
	/**
	* @var string
	 */
	protected $sUrl = '';
	
	/**
	 * @var string
	 */
	protected $sLocalMockup = '';
	
	protected function getUrl() {
		if (Config::getInstance()->get('useLocalDataMockup')) {
			return $this->sLocalMockup;
		}else {
			return $this->sUrl;
		}
	}
	
	protected function loadData($sUrl) {
		
		try {
            /*
             * 10 seconds timeout, just to be safe
             */
            $ctx = stream_context_create(array('http'=>
                array(
                    'timeout' => 5
                )
            ));

			$retVal = trim(file_get_contents($sUrl, false, $ctx));
		}catch(\Exception $e) {
			throw new NetworkException($e->getMessage(), $e->getCode(), $e);
		}
		
		if ($retVal === false) {
			throw new NetworkException('Resource failed to load');
		}
		
		return $retVal;
	}
	
	protected function createCacheKey($aParams = null) {
        return new CacheKey($this, md5(serialize($aParams).$this->getUrl()));
	}

    /**
     * @param array $aParams
     * @return mixed|string
     */
    public function get($aParams = null) {

        $sFile = '';

		$oCacheKey = $this->createCacheKey($aParams);
		$cache = PhpCache::getInstance()->create();
		
		try {
		
			if (!$cache->check($oCacheKey)) {
				$sFile = $this->loadData($this->getUrl());
				$cache->set($oCacheKey, $sFile, $this->cacheTime);
			}else {
				$sFile = $cache->get($oCacheKey);
			}

		} catch (\Exception $e) {
			Debug::cThrow(null, $e, array());
		}

		return $sFile;
	}
	
	/**
	 * Force load new data from source
	 * @param array $aParams
	 */
	public function forceReload($aParams = null) {
		PhpCache::getInstance()->create()->set($this->createCacheKey($aParams), $this->loadData($this->getUrl()), $this->cacheTime);
	}
	
}

class NetworkException extends \Exception {
	
}