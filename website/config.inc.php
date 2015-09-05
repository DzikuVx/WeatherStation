<?php

$config = array();

/*
  instead of fake variable cache following other cache mechanisms can be used:
  - Memcached
  - Apc
  - Redis
*/
$config['cacheMethod'] = 'Variable';
$config['memcachedIP'] = "127.0.0.1";
$config['memcachedPort'] = 11211;

$config['useLocalDataMockup'] = false;

/*
 *
 * @var int
 */
$config['cityId'] = 3083829;
$config['cityName'] = 'Szczecin,PL';
