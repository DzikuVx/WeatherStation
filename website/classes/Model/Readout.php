<?php

namespace Model;

use phpCache\CacheKey;

class Readout extends Base implements \Interfaces\Model
{

	protected $selectList = "readouts_external.*";
	protected $tableName = "readouts_external";
	protected $tableJoin = "";
	protected $extraList = "";
	protected $selectCountField = "readouts_external.ReadoutID";	
	
	/**
	 * @var string
	 */
	protected $tableDateField = '';
	protected $registryIdField = "ReadoutID";
	
	/**
	 * Table fields definition
	 * @var array
	 */
	protected $tableFields = array(
			'ReadoutID',
			'Date',
			'Humidity',
			'Temperature'
	);

	public function getAverage($days = 1) {

        $oKey = new CacheKey(get_class($this).'::getAverage', $days);

        $rResult = $this->cache->get($oKey);

        if ($rResult === false) {
            $stamp = date('Y-m-d H:i',strtotime ( "-{$days} day" , time() ) );
            $rResult = $this->db->fetch($this->db->execute("SELECT AVG(Temperature) Temperature, AVG(Humidity) Humidity FROM {$this->tableName} WHERE Date>='{$stamp}'"));

            $this->cache->set($oKey, $rResult, self::CACHE_INTERVAL_HOUR * $days);
        }

        return $rResult;
	}
	
	public function getMin($days = 1) {

        $oKey = new CacheKey(get_class($this).'::getMin', $days);

        $rResult = $this->cache->get($oKey);

        if ($rResult === false) {
            $stamp = date('Y-m-d H:i', strtotime ( "-{$days} day" , time() ) );
            $rResult = $this->db->fetch($this->db->execute("SELECT MIN(Temperature) Temperature, MIN(Humidity) Humidity FROM {$this->tableName} WHERE Date>='{$stamp}'"));
            $this->cache->set($oKey, $rResult, self::CACHE_INTERVAL_HOUR * $days);
        }

        return $rResult;
	}
	
	public function getMax($days = 1) {

        $oKey = new CacheKey(get_class($this).'::getMax', $days);
        $rResult = $this->cache->get($oKey);

        if ($rResult === false) {
            $stamp = date('Y-m-d H:i',strtotime ( "-{$days} day" , time() ) );
            $rResult = $this->db->fetch($this->db->execute("SELECT MAX(Temperature) Temperature, MAX(Humidity) Humidity FROM {$this->tableName} WHERE Date>='{$stamp}'"));
            $this->cache->set($oKey, $rResult, self::CACHE_INTERVAL_HOUR * $days);
        }

        return $rResult;
	}
	
	public function getCurrent() {

        $oKey = new CacheKey(get_class($this).'::getCurrent', 0);
        $rResult = $this->cache->get($oKey);

        if ($rResult === false) {
		    $rResult = $this->db->fetch($this->db->execute("SELECT * FROM {$this->tableName} ORDER BY `Date` DESC LIMIT 1"));
            $this->cache->set($oKey, $rResult, self::CACHE_INTERVAL_5_MINUTES);
        }

		return $rResult;
		
	}
	
	public function getHistory($skip = 0, $limit = 25) {
		$retVal = array();

		$oKey = new CacheKey(get_class($this).'::getHistory', $skip.'|'.$limit);
		
		if (!$this->cache->check($oKey)) {
		
			$rResult = $this->db->execute("SELECT * FROM {$this->tableName} ORDER BY `Date` DESC LIMIT {$limit} OFFSET {$skip}");
	
			while ($tResult = $this->db->fetchAssoc($rResult)) {
				array_push($retVal, $tResult);
			}

            $this->cache->set($oKey, $retVal, self::CACHE_INTERVAL_HOUR);
			
		}else {
			$retVal = $this->cache->get($oKey);
		}
			
		return $retVal;
		
	}
	
	public function getDayAggregate($days = 7, $orderBy = "DESC") {
		$retVal = array();
	
		$oKey = new CacheKey(get_class($this).'::getDayAggregate', $days.'|'.$orderBy);
		
		if (!$this->cache->check($oKey)) {
		
			$rResult = $this->db->execute("select
						date(`Date`) Date, AVG(Temperature) Temperature 
						, AVG(Humidity) Humidity
						, MIN(Temperature) MinTemperature
						, MAX(Temperature) MaxTemperature
						, MIN(Humidity) MinHumidity
						, MAX(Humidity) MaxHumidity
					FROM
						{$this->tableName}
	    			where 
						`Date`>(SELECT DATETIME('now', '-{$days} day'))
					group by 
						date(`Date`)
					ORDER BY
						date(`Date`) {$orderBy}
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
	
	public function getHourAggregate($hours = 24, $orderBy = "DESC") {
		$retVal = array();

		$oKey = new CacheKey(get_class($this).'::getHourAggregate', $hours.'|'.$orderBy);

		if (!$this->cache->check($oKey)) {
		
			$rResult = $this->db->execute("select
					strftime('%Y-%m-%d %H:00:00', `Date`) Date
					, AVG(Temperature) Temperature
					, AVG(Humidity) Humidity
					, MIN(Temperature) MinTemperature
					, MAX(Temperature) MaxTemperature
					, MIN(Humidity) MinHumidity
					, MAX(Humidity) MaxHumidity
					FROM
						{$this->tableName}
					where
						datetime(`Date`)>(SELECT DATETIME('now', '-{$hours} hour'))
					group by
						strftime('%Y-%m-%d %H:00:00', `Date`)
					ORDER BY
						datetime(`Date`) {$orderBy}
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
	
	public function getMonthlyAggregate($days = 24, $orderBy = "DESC") {
		$retVal = array();
	
		$oKey = new CacheKey(get_class($this).'::getMonthlyAggregate', $days.'|'.$orderBy);
	
		if (!$this->cache->check($oKey)) {
	
			$rResult = $this->db->execute("select
					strftime('%Y-%m', `Date`) Date
					, AVG(Temperature) Temperature
					, AVG(Humidity) Humidity
					, MIN(Temperature) MinTemperature
					, MAX(Temperature) MaxTemperature
					, MIN(Humidity) MinHumidity
					, MAX(Humidity) MaxHumidity
					FROM
					{$this->tableName}
					where
					datetime(`Date`)>(SELECT DATETIME('now', '-{$days} day'))
					group by
					strftime('%Y-%m', `Date`)
					ORDER BY
					datetime(`Date`) {$orderBy}
					");
	
			while ($tResult = $this->db->fetchAssoc($rResult)) {
				array_push($retVal, $tResult);
			}

            $this->cache->set($oKey, $retVal, self::CACHE_INTERVAL_DAY);
				
		} else {
			$retVal = $this->cache->get($oKey);
		}
	
		return $retVal;
	}
	
}