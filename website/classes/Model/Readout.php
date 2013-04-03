<?php

namespace Model;

class Readout extends Base implements \Interfaces\Model
{

	protected $tableDateField = '';
	protected $selectList = "readouts.*";
	protected $tableName = "readouts";
	protected $tableJoin = "";
	protected $extraList = "";
	protected $selectCountField = "readouts.ReadoutID";
	protected $registryIdField = "ReadoutID";
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
		
		$db = \Database\Factory::getInstance();
		
		$rResult = $db->execute("SELECT * FROM {$this->tableName} ORDER BY `Date` DESC LIMIT {$limit} OFFSET {$skip}");

		while ($tResult = $db->fetchAssoc($rResult)) {
			array_push($retVal, $tResult);
		}
		
		return $retVal;
		
	}
	
}