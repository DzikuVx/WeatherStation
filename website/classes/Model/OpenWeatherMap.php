<?php

namespace Model;
use PhpCache\CacheKey;

/**
 * Class gets data from OpenWeatherMap stored in external_data table
 * @author pawel
 *
 */
class OpenWeatherMap extends Base implements \Interfaces\Model {

	protected $tableName = 'external_data';

	protected $tableFields = array(
			'Date',
			'Pressure',
			'WindSpeed',
			'WindDirection'
	);

	public function getCurrent() {

		$rResult = $this->db->execute("SELECT * FROM {$this->tableName} ORDER BY `Date` DESC LIMIT 1");

		return $this->db->fetch($rResult);

	}

	public function getAverage($days = 1) {

		$stamp = date('Y-m-d H:i',strtotime ( "-{$days} day" , time() ) );

		$rResult = $this->db->execute("SELECT AVG(Pressure) Pressure, AVG(WindSpeed) WindSpeed FROM {$this->tableName} WHERE Date>='{$stamp}'");

		return $this->db->fetch($rResult);

	}

	public function getMin($days = 1) {

		$stamp = date('Y-m-d H:i',strtotime ( "-{$days} day" , time() ) );

		$rResult = $this->db->execute("SELECT MIN(Pressure) Pressure, MIN(WindSpeed) WindSpeed FROM {$this->tableName} WHERE Date>='{$stamp}'");

		return $this->db->fetch($rResult);

	}

	public function getMax($days = 1) {

		$stamp = date('Y-m-d H:i',strtotime ( "-{$days} day" , time() ) );

		$rResult = $this->db->execute("SELECT MAX(Pressure) Pressure, MAX(WindSpeed) WindSpeed FROM {$this->tableName} WHERE Date>='{$stamp}'");

		return $this->db->fetch($rResult);

	}

	public function getHourAggregate($hours = 24, $orderBy = "DESC") {
		$retVal = array();

		$oKey = new CacheKey(get_class($this).'::getHourAggregate', $hours.'|'.$orderBy);

		if (!$this->cache->check($oKey)) {

			$rResult = $this->db->execute("select
					strftime('%Y-%m-%d %H:00:00', `Date`) Date
					, AVG(Pressure) Pressure
					, AVG(WindSpeed) WindSpeed
					, MIN(Pressure) MinPressure
					, MAX(Pressure) MaxPressure
					, MIN(WindSpeed) MinWindSpeed
					, MAX(WindSpeed) MaxWindSpeed
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

	public function getDayAggregate($days = 7, $orderBy = "DESC") {
		$retVal = array();

		$oKey = new CacheKey(get_class($this).'::getDayAggregate', $days.'|'.$orderBy);
		
		if (!$this->cache->check($oKey)) {

			$rResult = $this->db->execute("select
					date(`Date`) Date, AVG(Pressure) Pressure
					, AVG(WindSpeed) WindSpeed
					, MIN(Pressure) MinPressure
					, MAX(Pressure) MaxPressure
					, MIN(WindSpeed) MinWindSpeed
					, MAX(WindSpeed) MaxWindSpeed
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

	public function getMonthlyAggregate($days = 24, $orderBy = "DESC") {
		$retVal = array();
	
		$oKey = new CacheKey(get_class($this).'::getMonthlyAggregate', $days.'|'.$orderBy);
	
		if (!$this->cache->check($oKey)) {
	
			$rResult = $this->db->execute("select
					strftime('%Y-%m', `Date`) Date
					, AVG(Pressure) Pressure
					, AVG(WindSpeed) WindSpeed
					, MIN(Pressure) MinPressure
					, MAX(Pressure) MaxPressure
					, MIN(WindSpeed) MinWindSpeed
					, MAX(WindSpeed) MaxWindSpeed
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