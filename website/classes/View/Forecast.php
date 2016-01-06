<?php

namespace View;

use General\Templater;

class Forecast extends Base {

	public function mainpage()
	{
		$oTemplate = new Templater('forecast.html');

		$proxyFactory = new \Factory\Proxy();
		
		$oTemplate->add('proxyForecast', $proxyFactory->create('Forecast')->get());
		
		return (string) $oTemplate;
		
	}
	
}