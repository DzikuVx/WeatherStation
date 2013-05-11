<?php

namespace Model;

use Model\Readout;


class InternalReadout extends Readout implements \Interfaces\Model {

	protected $selectList = "readouts.*";
	protected $tableName = "readouts";
	protected $tableJoin = "";
	protected $extraList = "";
	protected $selectCountField = "readouts.ReadoutID";
	
}