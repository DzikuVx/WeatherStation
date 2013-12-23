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
		
		$db = \Database\Factory::getInstance();
		
		$stamp = date('Y-m-d H:i',strtotime ( "-{$days} day" , time() ) );
		
		$rResult = $db->execute("SELECT AVG(Temperature) Temperature, AVG(Humidity) Humidity FROM {$this->tableName} WHERE Date>='{$stamp}'");
		
		return $db->fetch($rResult);
		
	}
	
	public function getMin($days = 1) {
		
		$db = \Database\Factory::getInstance();
		
		$stamp = date('Y-m-d H:i',strtotime ( "-{$days} day" , time() ) );
		
		$rResult = $db->execute("SELECT MIN(Temperature) Temperature, MIN(Humidity) Humidity FROM {$this->tableName} WHERE Date>='{$stamp}'");
		
		return $db->fetch($rResult);
		
	}
	
	public function getMax($days = 1) {
		
		$db = \Database\Factory::getInstance();
		
		$stamp = date('Y-m-d H:i',strtotime ( "-{$days} day" , time() ) );
		
		$rResult = $db->execute("SELECT MAX(Temperature) Temperature, MAX(Humidity) Humidity FROM {$this->tableName} WHERE Date>='{$stamp}'");
		
		return $db->fetch($rResult);
		
	}
	
	public function getCurrent() {
		
		$db = \Database\Factory::getInstance();
		
		$rResult = $db->execute("SELECT * FROM {$this->tableName} ORDER BY `Date` DESC LIMIT 1");
		
		return $db->fetch($rResult);
		
	}
	
	public function getHistory($skip = 0, $limit = 25) {
		$retVal = array();

		$cache = \phpCache\Factory::getInstance()->create();
		
		$oKey = new CacheKey(get_class($this).'::getHistory', $skip.'|'.$limit);
		
		if (!$cache->check($oKey)) {
		
			$db = \Database\Factory::getInstance();
			
			$rResult = $db->execute("SELECT * FROM {$this->tableName} ORDER BY `Date` DESC LIMIT {$limit} OFFSET {$skip}");
	
			while ($tResult = $db->fetchAssoc($rResult)) {
				array_push($retVal, $tResult);
			}
		
			$cache->set($oKey, $retVal, 300);
			
		}else {
			$retVal = $cache->get($oKey);
		}
			
		return $retVal;
		
	}
	
	public function getDayAggregate($days = 7, $orderBy = "DESC") {
		$retVal = array();
	
		$cache = \phpCache\Factory::getInstance()->create();
	
		$oKey = new CacheKey(get_class($this).'::getDayAggregate', $days.'|'.$orderBy);
		
		if (!$cache->check($oKey)) {
		
			$db = \Database\Factory::getInstance();
		
			$rResult = $db->execute("select 
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
		
			while ($tResult = $db->fetchAssoc($rResult)) {
				array_push($retVal, $tResult);
			}
	
			$cache->set($oKey, $retVal, 3600);
			
		}else {
			$retVal = $cache->get($oKey);
		}
		
		return $retVal;
	
	}
	
	public function getHourAggregate($hours = 24, $orderBy = "DESC") {
		$retVal = array();

		$cache = \phpCache\Factory::getInstance()->create();

		$oKey = new CacheKey(get_class($this).'::getHourAggregate', $hours.'|'.$orderBy);

		if (!$cache->check($oKey)) {
		
			$db = \Database\Factory::getInstance();
	
			$rResult = $db->execute("select
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
		
			while ($tResult = $db->fetchAssoc($rResult)) {
				array_push($retVal, $tResult);
			}
	
			$cache->set($oKey, $retVal, 3600);
			
		}else {
			$retVal = $cache->get($oKey);
		}
		
		return $retVal;

	}
	
	public function getMonthlyAggregate($days = 24, $orderBy = "DESC") {
		$retVal = array();
	
		$cache = \phpCache\Factory::getInstance()->create();
	
		$oKey = new CacheKey(get_class($this).'::getMonthlyAggregate', $days.'|'.$orderBy);
	
		if (!$cache->check($oKey)) {
	
			$db = \Database\Factory::getInstance();
	
			$rResult = $db->execute("select
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
	
			while ($tResult = $db->fetchAssoc($rResult)) {
				array_push($retVal, $tResult);
			}
	
			$cache->set($oKey, $retVal, 3600);
				
		}else {
			$retVal = $cache->get($oKey);
		}
	
		return $retVal;
	
	}
	
}