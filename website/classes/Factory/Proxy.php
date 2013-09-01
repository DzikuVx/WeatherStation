<?php

namespace Factory;

use Proxy\Forecast;

use Proxy\History;

use Interfaces\Factory;

use Proxy\AbstractProxy;
use Proxy\Current;

class Proxy implements Factory {
	
	/**
	 * 
	 * @param string $type
	 * @return AbstractProxy
	 */
	public function create($type) {
		
		switch ($type) {
			
			case 'Current':
				return new Current();
				break;
			
			case 'History':
				return new History();
				break;
			
			case 'Forecast':
				return new Forecast();
				break;
			
		}
		
	}
	
}