<?php

namespace Interfaces;
use General\Templater;

/**
 * 
 * @author Paweł
 *
 */
interface Listener {
	
	/**
	* @param array $aParams
	* @param Templater $template
	*/
	public function register(array &$aParams, Templater $template);
	
}