<?php

namespace View;

use General\Formater;

use General\Templater;

use Database\Factory;

abstract class Frontpage extends Base {

	protected $model = null;
	
	protected $modelName = '';
	
	public function __construct(array $aParams) {
		parent::__construct($aParams);
		
		$this->model = new $this->modelName();
		
	}
	
	public function mainpage()
	{
		$oTemplate = new Templater('mainpage.html');

		$oCurrent = $this->model->getCurrent();
		$oTemplate->add($oCurrent);

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
		$aHistory = $this->model->getDayAggregate(14);
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
		
		return (string) $oTemplate;
	}

	/**
	 * render average temperature chart head for google charts
	 * @return string
	 */
	public function chartHead() {
		
		$oTemplate = new Templater('chartHead.html');
		
		$aHistory = $this->model->getDayAggregate(14,"ASC");
		
		$aData = array();
		foreach ($aHistory as $iIndex => $oReadout) {
			$aData[] = "['".Formater::formatDate($oReadout['Date'])."', ".number_format($oReadout['Temperature'],2)."]";
		}
		
		$oTemplate->add('data',implode(',', $aData));
		
		$aData = array();
		foreach ($aHistory as $iIndex => $oReadout) {
			$aData[] = "['".Formater::formatDate($oReadout['Date'])."', ".number_format($oReadout['Humidity'],2)."]";
		}
		
		$oTemplate->add('dataHumidity',implode(',', $aData));
		
		/*
		 * 24 hours charts
		 */
		$aHistory = $this->model->getHourAggregate(24,"ASC");
		$aData = array();
		foreach ($aHistory as $iIndex => $oReadout) {
			$aData[] = "['".Formater::formatTime($oReadout['Date'])."', ".number_format($oReadout['Temperature'],2)."]";
		}
		$oTemplate->add('chartHourTemperature',implode(',', $aData));
		
		$aData = array();
		foreach ($aHistory as $iIndex => $oReadout) {
			$aData[] = "['".Formater::formatTime($oReadout['Date'])."', ".number_format($oReadout['Humidity'],2)."]";
		}
		$oTemplate->add('chartHourHumidity',implode(',', $aData));
		
		
		return (string) $oTemplate;
		
	}
	
}