<?php

namespace Listeners;
use General\Templater;

/**
 * 
 * Listener messageów
 * @author Paweł
 */
class Message implements \Interfaces\Singleton, \Interfaces\Listener {
	
	private static $instance;
	
	/**
	 * Konstruktor prywatny
	 */
	private function __construct() {
	
	}
	
	static public function getInstance() {
	
		if (empty(self::$instance)) {
			self::$instance = new self();
		}
	
		if (empty(self::$instance)) {
			throw new \Exception('Listener was unable to initiate');
		}
	
		return self::$instance;
	}
	
	/**
	 * Zapisanie tablicy kolejki do sesji
     * @param array $aArray
	 */
	private function set($aArray) {
		\General\Session::set('MessageQueue', $aArray);
	}
	
	/**
	 * 
	 * Pobranie tablicy kolejki
	 * @return array
	 */
	private function get() {
		return \General\Session::get('MessageQueue');
	}
	
	/**
	 * Wyczyszczenie kolejki komunikatów
	 */
	private function clear() {
		$this->set(null);
	}
	
	/**
	 * Wstawienie komunikatu do kolejki
	 * @param string $sType success/info/warning/error
	 * @param string $sMessage
	 */
	public function push($sType, $sMessage) {
		$aArray = $this->get();
		
		if (empty($aArray)) {
			$aArray = array();
		}
		
		array_push($aArray, array('type'=>$sType,'message'=>$sMessage));
		$this->set($aArray);
	}
	
	/**
	* Rejestracja listenera
	* @param array $aParams
	* @param Templater $template
	*/
	public function register(array &$aParams, Templater $template) {
	
		$aArray = $this->get();
	
		if (empty($aArray)) {
			return;
		}

		foreach ($aArray as $aMessage) {
			LowLevelMessage::getInstance()->push($aMessage['type'], $aMessage['message']);
		}
		
		$this->clear();
		
	}
	
}