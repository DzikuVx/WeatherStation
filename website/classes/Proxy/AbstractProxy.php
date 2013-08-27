<?php
namespace Proxy;

use Interfaces\Proxy;

abstract class AbstractProxy implements Proxy{
	
	/**
	 * @return string
	 */
	public function get($params = null) {
		return '';
	}
	
}