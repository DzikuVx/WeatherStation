<?php
use psDebug\Debug;

require_once 'common.php';

$aProxies = array('Forecast', 'Current');

$proxyFactory = new \Factory\Proxy();

foreach ($aProxies as $sProxy) {

	try {
		$proxyFactory->create($sProxy)->forceReload();
	} catch (\Exception $e) {
		Debug::cThrow(null, $e, array());		
	}
	
}