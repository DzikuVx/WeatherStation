<?php

namespace Interfaces;

/**
 * 
 * Interface listenerów systemu
 * @author Paweł
 *
 */
interface Listener {
	
	/**
	* Rejestracja listenera
	* @param array $aParams
	* @param \General\Templater $template
	*/
	public function register(array &$aParams, \General\Templater $template);
	
}