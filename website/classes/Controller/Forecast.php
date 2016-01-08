<?php

namespace Controller;

use Exception;
use General\Templater;
use Interfaces\Singleton;

class Forecast extends Base implements Singleton {

	protected $aExcluded = array();

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
			throw new Exception('Controller was unable to initiate');
		}

		return self::$instance;
	}

	

	public function render(array $aParams, Templater $template) {

		if (empty($aParams['type'])) {
			$aParams['type'] = 'chart';
		}

		$oView = new \View\Forecast($aParams);
		$template->add('menu-active-forecast','active');

		$template->add('mainContent', $oView->mainpage());

	}
}