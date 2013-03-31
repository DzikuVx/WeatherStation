<?php
namespace View;

abstract class Base implements \Interfaces\View {
	 
	protected $aParams = null;
	
	/**
	 * Konstruktor
	 * @param array $aParams
	 */
	public function __construct(array $aParams) {
		$this->aParams = $aParams;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Interfaces.View::get()
	 */
	public function get() {
		return '';
	}
	
}