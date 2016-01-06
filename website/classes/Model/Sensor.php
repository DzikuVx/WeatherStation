<?php

namespace Model;


use Database\Factory;
use Database\SQLiteWrapper;
use PhpCache\CacheKey;
use PhpCache\PhpCache;
use PhpCache\Redis;

class Sensor
{

    const CACHE_INTERVAL_DAY = 86400;
    const CACHE_INTERVAL_HOUR = 3600;
    const CACHE_INTERVAL_HALF_HOUR = 1800;
    const CACHE_INTERVAL_5_MINUTES = 300;
    const CACHE_INTERVAL_WEEK = 604800;

    const SENSOR_TEMPERATURE_EXTERNAL = 0;
    const SENSOR_HUMIDITY_EXTERNAL = 1;
    const SENSOR_PRESSURE_EXTERNAL = 2;
    const SENSOR_PRESSURE_API = 3;
    const SENSOR_WIND_SPEED_API = 4;
    const SENSOR_WIND_DIRECTION_API = 5;
    const SENSOR_TEMPERATURE_INTERNAL = 6;

    /**
     * @var array
     */
    protected $aParams = array();

    /** @var  SQLiteWrapper */
    protected $db;

    /** @var  Redis */
    protected $cache;

    /**
     * @param array $aParams
     */
    public function __construct(array $aParams = null)
    {
        $this->aParams = $aParams;
        $this->db = Factory::getInstance();
        $this->cache = PhpCache::getInstance()->create();
    }

    /**
     * @return string
     * @throws \Database\Exception
     */
    public function getLastReadoutDate() {
        $oKey = new CacheKey(get_class($this).'::getLastReadoutDate', 0);
        $rResult = $this->cache->get($oKey);

        if ($rResult === false) {
            $rResult = $this->db->fetch($this->db->execute("SELECT datetime(`Date`, 'unixepoch') `Date2` FROM `sensor_values` INDEXED BY SENSOR_A WHERE `Sensor` >= 0 ORDER BY `Date` DESC LIMIT 1"));
            $rResult = $rResult->Date2;
            $this->cache->set($oKey, $rResult, self::CACHE_INTERVAL_5_MINUTES);
        }
        return $rResult;
    }

    /**
     * @param $sensor
     * @return float
     * @throws \Database\Exception
     */
    public function getCurrent($sensor) {

        $oKey = new CacheKey(get_class($this).'::getCurrent', $sensor);
        $rResult = $this->cache->get($oKey);

        if ($rResult === false) {
            $rResult = $this->db->fetch($this->db->execute("SELECT `Value` FROM `sensor_values` INDEXED BY SENSOR_A WHERE `Sensor`={$sensor} ORDER BY `Date` DESC LIMIT 1"));
            $this->cache->set($oKey, $rResult->Value, self::CACHE_INTERVAL_5_MINUTES);
            return $rResult->Value;
        } else {
            return $rResult;
        }

    }

    public function getAverage($sensor, $days = 1) {

        $oKey = new CacheKey(get_class($this).'::getAverage', $sensor .'|' . $days);

        $rResult = $this->cache->get($oKey);

        if ($rResult === false) {
            $stamp = strtotime ( "-{$days} day" , time());
            $rResult = $this->db->fetch($this->db->execute("SELECT AVG(Value) Value FROM `sensor_values` INDEXED BY SENSOR_A WHERE Date>='{$stamp}' AND Sensor=$sensor"));
            $this->cache->set($oKey, $rResult->Value, self::CACHE_INTERVAL_HOUR * $days);

            return $rResult->Value;
        } else {
            return $rResult;
        }
    }

    public function getMin($sensor, $days = 1) {

        $oKey = new CacheKey(get_class($this).'::getMin', $sensor .'|' . $days);

        $rResult = $this->cache->get($oKey);

        if ($rResult === false) {
            $stamp = strtotime ( "-{$days} day" , time());
            $rResult = $this->db->fetch($this->db->execute("SELECT MIN(Value) Value FROM `sensor_values` INDEXED BY SENSOR_A WHERE Date>='{$stamp}' AND Sensor=$sensor"));
            $this->cache->set($oKey, $rResult->Value, self::CACHE_INTERVAL_HOUR * $days);
            return $rResult->Value;
        } else {
            return $rResult;
        }


    }

    public function getMax($sensor, $days = 1) {

        $oKey = new CacheKey(get_class($this).'::getMax', $sensor .'|' . $days);

        $rResult = $this->cache->get($oKey);

        if ($rResult === false) {
            $stamp = strtotime ( "-{$days} day" , time());
            $rResult = $this->db->fetch($this->db->execute("SELECT MAX(Value) Value FROM `sensor_values` INDEXED BY SENSOR_A WHERE Date>='{$stamp}' AND Sensor=$sensor"));
            $this->cache->set($oKey, $rResult->Value, self::CACHE_INTERVAL_HOUR * $days);
            return $rResult->Value;
        } else {
            return $rResult;
        }
    }

    public function getHourAggregate($sensor, $hours = 24, $orderBy = "DESC") {
        $retVal = array();

        $oKey = new CacheKey(get_class($this).'::getHourAggregate', $sensor . '|' . $hours.'|'.$orderBy);

        if (!$this->cache->check($oKey)) {

            $rResult = $this->db->execute("select
					strftime('%Y-%m-%d %H:00:00', `Date`, 'unixepoch') Date
					, AVG(Value) Avg
					, MIN(Value) Min
					, MAX(Value) Max
					FROM
						`sensor_values` INDEXED BY SENSOR_A
					where
						`Date`>(SELECT strftime('%s', 'now', '-{$hours} hour')) AND Sensor=$sensor
					group by
						strftime('%Y-%m-%d %H:00:00', `Date`, 'unixepoch')
					ORDER BY
						`Date` {$orderBy}
					");

            while ($tResult = $this->db->fetchAssoc($rResult)) {
                array_push($retVal, $tResult);
            }

            $this->cache->set($oKey, $retVal, self::CACHE_INTERVAL_HOUR);

        }else {
            $retVal = $this->cache->get($oKey);
        }

        return $retVal;

    }

    public function getDayAggregate($sensor, $days = 7, $orderBy = "DESC") {
        $retVal = array();

        $oKey = new CacheKey(get_class($this).'::getDayAggregate', $sensor . '|' . $days.'|'.$orderBy);

        if (!$this->cache->check($oKey)) {

            $rResult = $this->db->execute("select
					date(`Date`, 'unixepoch') Date
					, AVG(Value) Avg
					, MIN(Value) Min
					, MAX(Value) Max
					FROM
						`sensor_values` INDEXED BY SENSOR_A
					where
						`Date`>(SELECT strftime('%s', 'now', '-{$days} day')) AND Sensor=$sensor
					group by
						date(`Date`, 'unixepoch')
					ORDER BY
						date(`Date`, 'unixepoch') {$orderBy}
					");

            while ($tResult = $this->db->fetchAssoc($rResult)) {
                array_push($retVal, $tResult);
            }

            $this->cache->set($oKey, $retVal, self::CACHE_INTERVAL_HOUR * 8);

        }else {
            $retVal = $this->cache->get($oKey);
        }

        return $retVal;

    }

    public function getMonthlyAggregate($sensor, $days = 90, $orderBy = "DESC") {
        $retVal = array();

        $oKey = new CacheKey(get_class($this).'::getMonthlyAggregate', $sensor . '|' . $days.'|'.$orderBy);

        if (!$this->cache->check($oKey)) {

            $rResult = $this->db->execute("select
					strftime('%Y-%m', `Date`, 'unixepoch') Date
					, AVG(Value) Avg
					, MIN(Value) Min
					, MAX(Value) Max
					FROM
						`sensor_values` INDEXED BY SENSOR_A
					where
						`Date`>(SELECT strftime('%s', 'now', '-{$days} day')) AND Sensor=$sensor
					group by
						strftime('%Y-%m', `Date`, 'unixepoch')
					ORDER BY
						strftime('%Y-%m', `Date`, 'unixepoch') {$orderBy}
					");

            while ($tResult = $this->db->fetchAssoc($rResult)) {
                array_push($retVal, $tResult);
            }

            $this->cache->set($oKey, $retVal, self::CACHE_INTERVAL_DAY);

        }else {
            $retVal = $this->cache->get($oKey);
        }

        return $retVal;

    }

}