<?php

namespace View;

use General\Formater;

use General\Templater;

use Database\Factory;

class Forecast extends Base {

	protected $model = null;
	
	public function __construct(array $aParams) {
		parent::__construct($aParams);
		
		$this->model = new \Model\Readout();
		
	}
	
	public function mainpage()
	{
		$oTemplate = new Templater('forecast.html');

		$proxyFactory = new \Factory\Proxy();
		
		$oTemplate->add('proxyForecast', $proxyFactory->create('Forecast')->get());
		
		return (string) $oTemplate;
		
	}
	
}