<?php
namespace Proxy;

use Interfaces\Proxy;

abstract class AbstractProxy implements Proxy{
	
	/**
	* @var int
	 */
	protected $cacheTime = 3600;
	
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
		$oRetVal = new \Cache\CacheKey($this, md5(serialize($aParams)));
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
			$sFile = $this->loadData($this->sUrl);
			$cache->set($oCacheKey, $sFile, $this->cacheTime);
		}else {
			$sFile = $cache->get($oCacheKey);
		}
		
		return $sFile;
	}
	
}

class NetworkException extends \Exception {
	
}