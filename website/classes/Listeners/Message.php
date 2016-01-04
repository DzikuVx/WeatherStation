<?php

namespace Listeners;
use General\Session;
use General\Templater;
use Interfaces\Listener;
use Interfaces\Singleton;

class Message implements Singleton, Listener {
	
	private static $instance;
	
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
     * @param array $aArray
	 */
	private function set($aArray) {
		Session::set('MessageQueue', $aArray);
	}
	
	/**
	 * 
	 * @return array
	 */
	private function get() {
		return Session::get('MessageQueue');
	}
	
	private function clear() {
		$this->set(null);
	}
	
	/**
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