<?php

namespace View;

use General\Formater;
use General\Templater;

class Overview extends Base {

	protected $model = null;
	
	public function __construct(array $aParams) {
		parent::__construct($aParams);
		
		$this->model = new \Model\Readout();
		
	}
	
	public function mainpage()
	{
		$oTemplate = new Templater('overview.html');

		$oCurrent = $this->model->getCurrent();
        $oCurrent->Temperature = Formater::formatFloat($oCurrent->Temperature, 0);
        $oCurrent->Humidity = Formater::formatFloat($oCurrent->Humidity, 0);

        $oTemplate->add($oCurrent);

        $oTemplate->add('LastReadout', $oCurrent->Date);

		$oData = $this->model->getAverage(1);
		$oTemplate->add('1dTempAvg', Formater::formatFloat($oData->Temperature, 2));
		$oTemplate->add('1dHumidityAvg', Formater::formatFloat($oData->Humidity, 2));
		
		$oData = $this->model->getMin(1);
		$oTemplate->add('1dTempMin', Formater::formatFloat($oData->Temperature, 2));
		$oTemplate->add('1dHumidityMin', Formater::formatFloat($oData->Humidity, 2));
		
		$oData = $this->model->getMax(1);
		$oTemplate->add('1dTempMax', Formater::formatFloat($oData->Temperature, 2));
		$oTemplate->add('1dHumidityMax', Formater::formatFloat($oData->Humidity, 2));
		
		$oData = $this->model->getAverage(7);
		$oTemplate->add('7dTempAvg', Formater::formatFloat($oData->Temperature, 2));
		$oTemplate->add('7dHumidityAvg', Formater::formatFloat($oData->Humidity, 2));
		
		$oData = $this->model->getMin(7);
		$oTemplate->add('7dTempMin', Formater::formatFloat($oData->Temperature, 2));
		$oTemplate->add('7dHumidityMin', Formater::formatFloat($oData->Humidity, 2));
		
		$oData = $this->model->getMax(7);
		$oTemplate->add('7dTempMax', Formater::formatFloat($oData->Temperature, 2));
		$oTemplate->add('7dHumidityMax', Formater::formatFloat($oData->Humidity, 2));

        /*
         * External data from Open Weather Map
         */
		$oOpenWeatherMap = new \Model\OpenWeatherMap();
		$oCurrent = $oOpenWeatherMap->getCurrent();

        $oCurrent->Pressure = Formater::formatFloat($oCurrent->Pressure, 0);
        $oCurrent->WindSpeed = Formater::formatFloat($oCurrent->WindSpeed, 0);
        $oCurrent->WindDirection = Formater::formatFloat(round($oCurrent->WindDirection / 10) * 10, 0);

		$oTemplate->add($oCurrent);
		
		$oData = $oOpenWeatherMap->getAverage(1);
		$oTemplate->add('1dPressureAvg', Formater::formatFloat($oData->Pressure, 2));
		$oTemplate->add('1dWindAvg', Formater::formatFloat($oData->WindSpeed, 2));
		
		$oData = $oOpenWeatherMap->getMin(1);
		$oTemplate->add('1dPressureMin', Formater::formatFloat($oData->Pressure, 2));
		$oTemplate->add('1dWindMin', Formater::formatFloat($oData->WindSpeed, 2));
		
		$oData = $oOpenWeatherMap->getMax(1);
		$oTemplate->add('1dPressureMax', Formater::formatFloat($oData->Pressure, 2));
		$oTemplate->add('1dWindMax', Formater::formatFloat($oData->WindSpeed, 2));
		
		$oData = $oOpenWeatherMap->getAverage(7);
		$oTemplate->add('7dPressureAvg', Formater::formatFloat($oData->Pressure, 2));
		$oTemplate->add('7dWindAvg', Formater::formatFloat($oData->WindSpeed, 2));
		
		$oData = $oOpenWeatherMap->getMin(7);
		$oTemplate->add('7dPressureMin', Formater::formatFloat($oData->Pressure, 2));
		$oTemplate->add('7dWindMin', Formater::formatFloat($oData->WindSpeed, 2));
		
		$oData = $oOpenWeatherMap->getMax(7);
		$oTemplate->add('7dPressureMax', Formater::formatFloat($oData->Pressure, 2));
		$oTemplate->add('7dWindMax', Formater::formatFloat($oData->WindSpeed, 2));
		
		$proxyFactory = new \Factory\Proxy();
		
		$oTemplate->add('proxyCurrent', $proxyFactory->create('Current')->get());
		$oTemplate->add('proxyForecast', $proxyFactory->create('Forecast')->get());

		return (string) $oTemplate;
	}

	public function charts()
	{
		$oTemplate = new Templater('charts.html');
	
		return (string) $oTemplate;
	}

	/**
	 * render average temperature chart head for google charts
	 * @return string
	 */
	public function chartHead() {
		
		$t = \Translate\Controller::getDefault();
		
		$oTemplate = new Templater('chartHead.html');
		
		/**
		 * Data from OpenWeatherMap.org
		 */
		$oOpenWeatherMap = new \Model\OpenWeatherMap();
		
		$aHistory = $oOpenWeatherMap->getHourAggregate(168,"ASC");

		$oChartHourPressure = new \General\GoogleChart();
		$oChartHourPressure->setTitle($t->get('Pressure') . " [hPa]");
		$oChartHourPressure->setDomID('chartHourPressure');
		$oChartHourPressure->add('Hour', array());
		$oChartHourPressure->add('Min', array());
		
		foreach ($aHistory as $oReadout) {
			
			$oChartHourPressure->push('Hour', Formater::formatTime($oReadout['Date']));
			$oChartHourPressure->push('Min', number_format($oReadout['MinPressure'],2,'.',''));
			
		}
		$oTemplate->add('chartHourPressure',$oChartHourPressure->getHead());

        $oChartHourWindSpeed = new \General\GoogleChart();
        $oChartHourWindSpeed->setTitle($t->get('Wind speed') . " [m/s]");
        $oChartHourWindSpeed->setDomID('chartHourWindSpeed');
        $oChartHourWindSpeed->add('Hour', array());
        $oChartHourWindSpeed->add('Min', array());

        foreach ($aHistory as $oReadout) {

            $oChartHourWindSpeed->push('Hour', Formater::formatTime($oReadout['Date']));
            $oChartHourWindSpeed->push('Min', number_format($oReadout['WindSpeed'],2,'.',''));

        }
        $oTemplate->add('chartHourWindSpeed',$oChartHourWindSpeed->getHead());

		/*
		 * Hour Aggregate charts
		 */
		$aHistory = $this->model->getHourAggregate(168,"ASC");
		
		$oChartHourTemperature = new \General\GoogleChart();
		$oChartHourTemperature->setTitle($t->get('Temperature') . " [C]");
		$oChartHourTemperature->setDomID('chartHourTemperature');
		$oChartHourTemperature->add('Hour', array());
		$oChartHourTemperature->add('Avg', array());
		
		$oChartHourHumidity = new \General\GoogleChart();
		$oChartHourHumidity->setTitle($t->get('Humidity') . " [%]");
		$oChartHourHumidity->setDomID('chartHourHumidity');
		$oChartHourHumidity->add('Hour', array());
		$oChartHourHumidity->add('Avg', array());

		foreach ($aHistory as $oReadout) {
			
			$oChartHourTemperature->push('Hour', Formater::formatTime($oReadout['Date']));
			$oChartHourTemperature->push('Avg', number_format($oReadout['Temperature'],2));
			
			$oChartHourHumidity->push('Hour', Formater::formatTime($oReadout['Date']));
			$oChartHourHumidity->push('Avg', number_format($oReadout['Humidity'],2));
			
		}
		$oTemplate->add('chartHourTemperature',$oChartHourTemperature->getHead());
		$oTemplate->add('chartHourHumidity',$oChartHourHumidity->getHead());

		return (string) $oTemplate;
	}
	
}