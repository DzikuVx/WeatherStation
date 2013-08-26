<?php

namespace Controller;

class History extends Base implements \Interfaces\Singleton {

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

	public function render(array $aParams, \General\Templater $template) {

		if (empty($aParams['type'])) {
			$aParams['type'] = 'chart';
		}
		
		if (empty($aParams['range'])) {
			$aParams['range'] = 'month';
		}

		$oView = new \View\History($aParams);
		$template->add('menu-active-history','active');

		$template->add('mainContent', $oView->mainpage());

		switch ($aParams['type']) {
				
			case 'table':
				$template->add('pageContent', $oView->tables());
				$template->add('submenu-active-table','active');
				break;

			case 'chart':
			default:
				$template->add('chartHead', $oView->chartHead());
				$template->add('pageContent', $oView->charts());
				$template->add('submenu-active-chart','active');
// 				var_dump('here');
// 				die();
				break;

		}
	}
}