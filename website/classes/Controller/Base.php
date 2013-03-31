<?php

namespace Controller;

class Base implements \Interfaces\Singleton {

	protected $aExcluded = array();
	protected $aGlobalExcluded = array('getName','getPermissionTranslation');
	protected $aTranslation = array();

	/**
	 *
	 * Metoda pobiera nazwę kontrolera
	 * @return string
	 */
	public function getName() {
			
		$aClass = \General\StaticUtils::parseClassname(get_class($this));
			
		return "{T:{$aClass['classname']}}";
	}

	public function getExcluded()
	{
		return array_merge($this->aExcluded,$this->aGlobalExcluded);
	}

	private static $instance;

	/**
	 * Konstruktor prywatny
	 */
	private function __construct()
	{

	}

	static public function getInstance()
	{

		if (empty(self::$instance)) {
			self::$instance = new self();
		}

		if (empty(self::$instance)) {
			throw new \Exception('Base controller was unable to initiate');
		}

		return self::$instance;
	}


	static public function formatLink(array $aParams, $sAmp = true, $bOnClick = false)
	{
		$sRetVal = '';

		foreach ($aParams as $sKey => $sValue) {
			$sRetVal .= $sKey . '/' . $sValue;
		}
		
		$sRetVal = \General\Config::getInstance()->get('baseUrl') . $sRetVal;
		return $sRetVal;
	}

}