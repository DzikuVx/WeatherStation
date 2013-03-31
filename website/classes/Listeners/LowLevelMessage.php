<?php

namespace Listeners;

/**
 * 
 * Listener messageów
 * @author Paweł
 */
class LowLevelMessage implements \Interfaces\Singleton, \Interfaces\Listener {
	
	private static $instance;
        protected $message = array();
	
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
	 * 
	 * Zapisanie tablicy kolejki do sesji
	 * @param tabela kolejki $aArray
	 */
	private function set($aArray) {
		$this->message = $aArray;
	}
	
	/**
	 * 
	 * Pobranie tablicy kolejki
	 * @return array
	 */
	private function get() {
		return $this->message;
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
	 * Utworzenie kodu HTML z wiadomością
	 * @param array $aMessage
	 * @return string
	 */
	private function render(array $aMessage) {
		
		
		switch ($aMessage['type']) {
			
			case 'success':
				$sClass = 'alert alert-success';
				break;
				
			case 'info':
				$sClass = 'alert alert-info';
				break;

			case 'error':
				$sClass = 'alert alert-error';
				break;
			
			default:
				$sClass = 'alert';
				break;
			
		}
		
		$sHtml = '<div class="'.$sClass.'">'.$aMessage['message'].'</div>';
		
		return $sHtml;
	}
	
	/**
	* Rejestracja listenera
	* @param array $aParams
	* @param \General\Templater $template
	*/
	public function register(array &$aParams, \General\Templater $template) {
	
		$aArray = $this->get();
	
		if (empty($aArray)) {
			return;
		}
		
		$sHtml = '';
		
		foreach ($aArray as $aMessage) {
			$sHtml .= $this->render($aMessage);
		}
		
		$template->add('listeners',$sHtml);
		
		$this->clear();
		
	}
	
}