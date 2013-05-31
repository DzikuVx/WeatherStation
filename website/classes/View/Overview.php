<?php

namespace View;

use General\Formater;

use General\Templater;

use Database\Factory;

class Overview extends Base {

	protected $model = null;
	
	public function __construct(array $aParams) {
		parent::__construct($aParams);
		
		$this->model = new \Model\Readout();
		
	}
	
	public function mainpage()
	{
		$oTemplate = new Templater('mainpage.html');

		$oCurrent = $this->model->getCurrent();
		$oTemplate->add($oCurrent);

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
		$oTemplate->add($oCurrent);
		
		$oData = $oOpenWeatherMap->getAverage(1);
		$oTemplate->add('1dPressureAvg', Formater::formatFloat($oData->Pressure, 2));
		
		$oData = $oOpenWeatherMap->getMin(1);
		$oTemplate->add('1dPressureMin', Formater::formatFloat($oData->Pressure, 2));
		
		$oData = $oOpenWeatherMap->getMax(1);
		$oTemplate->add('1dPressureMax', Formater::formatFloat($oData->Pressure, 2));
		
		$oData = $oOpenWeatherMap->getAverage(7);
		$oTemplate->add('7dPressureAvg', Formater::formatFloat($oData->Pressure, 2));
		
		$oData = $oOpenWeatherMap->getMin(7);
		$oTemplate->add('7dPressureMin', Formater::formatFloat($oData->Pressure, 2));
		
		$oData = $oOpenWeatherMap->getMax(7);
		$oTemplate->add('7dPressureMax', Formater::formatFloat($oData->Pressure, 2));
		
		return (string) $oTemplate;
		
	}
	
	public function tables()
	{
		$oTemplate = new Templater('tables.html');

		/*
		 * Get readout history
		 */
		$aHistory = $this->model->getHistory();

		$sTable = '';
		foreach ($aHistory as $iIndex => $oReadout) {
			
			$sTable .= '<tr>';
			$sTable .= '<td>'.($iIndex+1).'</td>';
			$sTable .= '<td>'.Formater::formatDateTime($oReadout['Date']).'</td>';
			$sTable .= '<td>'.$oReadout['Temperature'].'&deg;C</td>';
			$sTable .= '<td>'.$oReadout['Humidity'].'%</td>';
			$sTable .= '</tr>';
			
		}
		$oTemplate->add('Table', $sTable);
		
		/*
		 * Get daily aggregate
		 */
		$aHistory = $this->model->getDayAggregate(30);
		$sTable = '';
		foreach ($aHistory as $iIndex => $oReadout) {
			
			$sTable .= '<tr>';
			$sTable .= '<td>'.Formater::formatDate($oReadout['Date']).'</td>';
			$sTable .= '<td>'.Formater::formatFloat($oReadout['MinTemperature'],2).'&deg;C</td>';
			$sTable .= '<td>'.Formater::formatFloat($oReadout['Temperature'],2).'&deg;C</td>';
			$sTable .= '<td>'.Formater::formatFloat($oReadout['MaxTemperature'],2).'&deg;C</td>';
			$sTable .= '<td>'.Formater::formatFloat($oReadout['MinHumidity'],2).'%</td>';
			$sTable .= '<td>'.Formater::formatFloat($oReadout['Humidity'],2).'%</td>';
			$sTable .= '<td>'.Formater::formatFloat($oReadout['MaxHumidity'],2).'%</td>';
			$sTable .= '</tr>';
			
		}
		$oTemplate->add('DailyTable', $sTable);
		
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
		
		$oTemplate = new Templater('chartHead.html');
		
		/**
		 * Data from OpenWeatherMap.orh
		 */
		$oOpenWeatherMap = new \Model\OpenWeatherMap();
		
		$aHistory = $oOpenWeatherMap->getHourAggregate(168,"ASC");

		$oChartHourPressure = new \General\GoogleChart();
		$oChartHourPressure->setTitle('Pressure');
		$oChartHourPressure->setDomID('chartHourPressure');
		$oChartHourPressure->add('Hour', array());
		$oChartHourPressure->add('Avg', array());
		$oChartHourPressure->add('Max', array());
		$oChartHourPressure->add('Min', array());
		
		foreach ($aHistory as $iIndex => $oReadout) {
			
			$oChartHourPressure->push('Hour', Formater::formatTime($oReadout['Date']));
			$oChartHourPressure->push('Avg', number_format($oReadout['Pressure'],2,'.',''));
			$oChartHourPressure->push('Max', number_format($oReadout['MaxPressure'],2,'.',''));
			$oChartHourPressure->push('Min', number_format($oReadout['MinPressure'],2,'.',''));
			
		}
		$oTemplate->add('chartHourPressure',$oChartHourPressure->getHead());

		$oChartDailyPressure = new \General\GoogleChart();
		$oChartDailyPressure->setTitle('Pressure');
		$oChartDailyPressure->setDomID('chartDailyPressure');
		$oChartDailyPressure->add('Hour', array());
		$oChartDailyPressure->add('Avg', array());
		$oChartDailyPressure->add('Max', array());
		$oChartDailyPressure->add('Min', array());
		
		$aHistory = $oOpenWeatherMap->getDayAggregate(30,"ASC");
		foreach ($aHistory as $iIndex => $oReadout) {
			
			$oChartDailyPressure->push('Hour', Formater::formatDate($oReadout['Date']));
			$oChartDailyPressure->push('Avg', number_format($oReadout['Pressure'],2,'.',''));
			$oChartDailyPressure->push('Max', number_format($oReadout['MaxPressure'],2,'.',''));
			$oChartDailyPressure->push('Min', number_format($oReadout['MinPressure'],2,'.',''));
			
		}
		
		$oTemplate->add('chartDailyPressure',$oChartDailyPressure->getHead());
		
		
		/*
		 * Hour Aggregate charts
		 */
		$aHistory = $this->model->getHourAggregate(168,"ASC");
		
		$oChartHourTemperature = new \General\GoogleChart();
		$oChartHourTemperature->setTitle('Temperature');
		$oChartHourTemperature->setDomID('chartHourTemperature');
		$oChartHourTemperature->add('Hour', array());
		$oChartHourTemperature->add('Avg', array());
		$oChartHourTemperature->add('Max', array());
		$oChartHourTemperature->add('Min', array());
		
		$oChartHourHumidity = new \General\GoogleChart();
		$oChartHourHumidity->setTitle('Humidity');
		$oChartHourHumidity->setDomID('chartHourHumidity');
		$oChartHourHumidity->add('Hour', array());
		$oChartHourHumidity->add('Avg', array());
		$oChartHourHumidity->add('Max', array());
		$oChartHourHumidity->add('Min', array());

		foreach ($aHistory as $iIndex => $oReadout) {
			
			$oChartHourTemperature->push('Hour', Formater::formatTime($oReadout['Date']));
			$oChartHourTemperature->push('Avg', number_format($oReadout['Temperature'],2));
			$oChartHourTemperature->push('Max', number_format($oReadout['MaxTemperature'],2));
			$oChartHourTemperature->push('Min', number_format($oReadout['MinTemperature'],2));
			
			$oChartHourHumidity->push('Hour', Formater::formatTime($oReadout['Date']));
			$oChartHourHumidity->push('Avg', number_format($oReadout['Humidity'],2));
			$oChartHourHumidity->push('Max', number_format($oReadout['MaxHumidity'],2));
			$oChartHourHumidity->push('Min', number_format($oReadout['MinHumidity'],2));
			
		}
		$oTemplate->add('chartHourTemperature',$oChartHourTemperature->getHead());
		$oTemplate->add('chartHourHumidity',$oChartHourHumidity->getHead());

		/*
		 * Day aggregate charts
		 */
		
		$aHistory = $this->model->getDayAggregate(30,"ASC");
		
		$oChartDailyTemperature = new \General\GoogleChart();
		$oChartDailyTemperature->setTitle('Temperature');
		$oChartDailyTemperature->setDomID('chartDailyTemperature');
		$oChartDailyTemperature->add('Day', array());
		$oChartDailyTemperature->add('Avg', array());
		$oChartDailyTemperature->add('Max', array());
		$oChartDailyTemperature->add('Min', array());
		
		$oChartDailyHumidity = new \General\GoogleChart();
		$oChartDailyHumidity->setTitle('Humidity');
		$oChartDailyHumidity->setDomID('chartDailyHumidity');
		$oChartDailyHumidity->add('Day', array());
		$oChartDailyHumidity->add('Avg', array());
		$oChartDailyHumidity->add('Max', array());
		$oChartDailyHumidity->add('Min', array());
		
		foreach ($aHistory as $iIndex => $oReadout) {
				
			$oChartDailyTemperature->push('Day', Formater::formatDate($oReadout['Date']));
			$oChartDailyTemperature->push('Avg', number_format($oReadout['Temperature'],2));
			$oChartDailyTemperature->push('Max', number_format($oReadout['MaxTemperature'],2));
			$oChartDailyTemperature->push('Min', number_format($oReadout['MinTemperature'],2));
				
			$oChartDailyHumidity->push('Day', Formater::formatDate($oReadout['Date']));
			$oChartDailyHumidity->push('Avg', number_format($oReadout['Humidity'],2));
			$oChartDailyHumidity->push('Max', number_format($oReadout['MaxHumidity'],2));
			$oChartDailyHumidity->push('Min', number_format($oReadout['MinHumidity'],2));
				
		}
		$oTemplate->add('chartDailyTemperature',$oChartDailyTemperature->getHead());
		$oTemplate->add('chartDailyHumidity',$oChartDailyHumidity->getHead());
		
		return (string) $oTemplate;
		
	}
	
}