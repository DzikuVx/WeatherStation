<?php
namespace Proxy;

use General\Config;

use General\Debug;

use Interfaces\Proxy;

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
			$retVal = file_get_contents($sUrl);
		}catch(\Exception $e) {
			throw new NetworkException($e->getMessage(), $e->getCode(), $e);
		}
		
		if ($retVal === false) {
			throw new NetworkException('Resource failed to load');
		}
		
		return $retVal;
	}
	
	protected function createCacheKey($aParams = null) {
		$oRetVal = new \Cache\CacheKey($this, md5(serialize($aParams).$this->getUrl()));
		return $oRetVal;
	}
	
	/**
	 * @param array $params
	 * @return string
	 */
	public function get($aParams = null) {

		$oCacheKey = $this->createCacheKey($aParams);
		
		$cache = \Cache\Factory::getInstance();
		
		if (!$cache->check($oCacheKey)) {
			$sFile = $this->loadData($this->getUrl());
			$cache->set($oCacheKey, $sFile, $this->cacheTime);
		}else {
			$sFile = $cache->get($oCacheKey);
		}
		
		return $sFile;
	}
	
}

class NetworkException extends \Exception {
	
}