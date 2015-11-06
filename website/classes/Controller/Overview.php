<?php

namespace Controller;

use Exception;
use General\Templater;
use Interfaces\Singleton;

class Overview extends Base implements Singleton {

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

		$oView = new \View\Overview($aParams);
		$template->add('menu-active-overview','active');

		$template->add('mainContent', $oView->mainpage());

        $template->add('chartHead', $oView->chartHead());
        $template->add('pageContent', $oView->charts());
	}
}