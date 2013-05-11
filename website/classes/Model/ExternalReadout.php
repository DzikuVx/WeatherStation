<?php

namespace Model;

use Model\Readout;


class ExternalReadout extends Readout implements \Interfaces\Model {

	protected $selectList = "readouts_external.*";
	protected $tableName = "readouts_external";
	protected $tableJoin = "";
	protected $extraList = "";
	protected $selectCountField = "readouts_external.ReadoutID";
	
}