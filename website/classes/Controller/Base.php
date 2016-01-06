<?php

namespace Controller;

use General\Config;
use General\StaticUtils;
use General\Templater;
use Interfaces\Singleton;

class Base implements Singleton {

	protected $aExcluded = array();
	protected $aGlobalExcluded = array('getName','getPermissionTranslation');
	protected $aTranslation = array();

	/**
	 *
	 * @return string
	 */
	public function getName() {
			
		$aClass = StaticUtils::parseClassname(get_class($this));
			
		return "{T:{$aClass['classname']}}";
	}

	public function getExcluded()
	{
		return array_merge($this->aExcluded,$this->aGlobalExcluded);
	}

	private static $instance;

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


	static public function formatLink(array $aParams)
	{
		$sRetVal = '';

		foreach ($aParams as $sKey => $sValue) {
			$sRetVal .= $sKey . '/' . $sValue;
		}
		
		$sRetVal = Config::getInstance()->get('baseUrl') . $sRetVal;
		return $sRetVal;
	}

	/**
	 * @param array $aParams
	 * @param Templater $template
     */
	public function module(array $aParams, Templater $template) {
	
		if (isset($aParams['module'])) {
	
			$className = '\\Module\\'.$aParams['module'];
	
			if (class_exists($className)) {

                /** @noinspection PhpUndefinedMethodInspection */
                $tObject = $className::getInstance();
					
				if (method_exists($tObject, 'execute')) {
					$tObject->execute($aParams, $template);

                    /** @noinspection PhpUndefinedFieldInspection */
                    Main::$mainContentProcessed = true;
	
				}
			}
	
		}
	
	}
	
}