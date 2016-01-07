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
 * @var int
 */
$config['cityId'] = 3083829;
$config['cityName'] = 'Szczecin,PL';
$config['apiKey'] = '1111';

$config['language'] = 'en';

$config['timezone'] = 'Europe/Warsaw';

$config['sensors'] = array(
    array(
        'id' => 0,
        'name' => '{T:Temperature}',
        'symbol' => 'Temperature',
        'unit' => '&deg;C',
        'decimals' => 2,
        'show-in' => array(
            'overview', 'history-30', 'history-365', 'history-all'
        ),
        'graph-overview-setting' => array(
            'show' => array('avg'),
            'decimals' => 2
        ),
        'graph-history-setting' => array(
            'show' => array('min', 'avg', 'max'),
            'decimals' => 2
        ),
    ),
    array(
        'id' => 1,
        'name' => '{T:Humidity}',
        'symbol' => 'Humidity',
        'unit' => '%',
        'decimals' => 0,
        'show-in' => array(
            'overview', 'history-30', 'history-365', 'history-all'
        ),
        'graph-overview-setting' => array(
            'show' => array('avg'),
            'decimals' => 0
        ),
        'graph-history-setting' => array(
            'show' => array('min', 'avg'),
            'decimals' => 0
        ),
    ),
    array(
        'id' => 2,
        'name' => '{T:Pressure}',
        'symbol' => 'Pressure',
        'unit' => 'hPa',
        'decimals' => 0,
        'show-in' => array(
            'overview', 'history-30', 'history-365', 'history-all'
        ),
        'graph-overview-setting' => array(
            'show' => array('avg'),
            'decimals' => 0
        ),
        'graph-history-setting' => array(
            'show' => array('min', 'avg', 'max'),
            'decimals' => 0
        ),
    ),
    array(
        'id' => 4,
        'name' => '{T:Wind speed}',
        'symbol' => 'WindSpeed',
        'unit' => 'm/s',
        'decimals' => 1,
        'show-in' => array(
            'overview', 'history-30', 'history-365', 'history-all'
        ),
        'graph-overview-setting' => array(
            'show' => array('avg'),
            'decimals' => 1
        ),
        'graph-history-setting' => array(
            'show' => array('min', 'avg', 'max'),
            'decimals' => 1
        ),
    )
);