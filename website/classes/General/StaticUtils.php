<?php

namespace General;

class StaticUtils {

	private function __construct() {

	}

	static public function parseClassname ($name)
	{
		return array(
	    'namespace' => array_slice(explode('\\', $name), 0, -1),
	    'classname' => join('', array_slice(explode('\\', $name), -1)),
		);
	}

}