<?php

namespace Interfaces;

/**
 * 
 * @author Paweł
 *
 */
interface Listener {
	
	/**
	* @param array $aParams
	* @param \General\Templater $template
	*/
	public function register(array &$aParams, \General\Templater $template);
	
}