<?php
require_once 'common.php';

$aProxies = array('Forecast', 'Current', 'History');

$proxyFactory = new \Factory\Proxy();

foreach ($aProxies as $sProxy) {
	$proxyFactory->create($sProxy)->forceReload();
}