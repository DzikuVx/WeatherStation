<?php
namespace View;

use Interfaces\View;

abstract class Base implements View {
	 
	protected $aParams = null;
	
	/**
	 * @param array $aParams
	 */
	public function __construct(array $aParams) {
		$this->aParams = $aParams;
	}
	
	/**
	 * @see Interfaces.View::get()
	 */
	public function get() {
		return '';
	}
	
}