<?php

namespace General;

class GoogleChart {

	private $title = '';
	private $domID = '';
	
	private $data = array();
	
	public function setTitle($title) {
		$this->title = $title;
	}

	public function setDomID($domID) {
		$this->domID = $domID;
	}
	
	public function add($title, $data) {
		$this->data[$title] = $data;
	}

	public function push($title, $value) {
		array_push($this->data[$title], $value);
	}
	
	public function getHead() {

		$sRetVal = '';
		
		$aKeys = array_keys($this->data);

		$aOutKeys = array();
		foreach ($aKeys as $key=>$value) {
			$aOutKeys[$key] = "'".$value."'";
		}
		
		$sRetVal .= "var data = google.visualization.arrayToDataTable([";
        $sRetVal .= "[ ".implode(", ", $aOutKeys)."]";
        
        $iLength = count($this->data[$aKeys[0]]);
        
        for ($i = 0; $i < $iLength; $i++) {
        	$aTemp = array();

        	foreach ($aKeys as $key=>$value) {
        		
        		$chartVal = $this->data[$aKeys[$key]][$i];
        		
        		if (!is_numeric($chartVal)) {
        			$chartVal = "'".$chartVal."'";
        		}
        		
        		array_push($aTemp, $chartVal);
        	}
        	
	        $sRetVal .= ", [ ".implode(", ", $aTemp)."]";
        }
        
        $sRetVal .= "]);";
		
		$sRetVal .= "\n\rvar options = {
            			title : '{$this->title}',
            			legend: {position: 'right'}
            		};";
		
		$sRetVal .= "\n\rvar chart = new google.visualization.LineChart(document
   				.getElementById('{$this->domID}'));
   		chart.draw(data, options);";
		
		
		return $sRetVal;
		
	}
	
}