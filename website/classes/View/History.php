<?php

namespace View;

use General\Formater;
use General\Templater;
use Model\Readout;
use Model\OpenWeatherMap;

class History extends Base {

	/**
	 * @var \Model\Readout
	 */
	protected $model = null;
	
	/**
	 * @var \Model\Readout
	 */
	protected $externalModel = null;

	protected $internalData = array();
	protected $externalData = array();

	public function __construct(array $aParams) {
		parent::__construct($aParams);

		$this->model = new Readout();
		$this->externalModel = new OpenWeatherMap();

		switch($aParams['range']) {
			
			case 'year':
				$this->internalData = $this->model->getMonthlyAggregate(365,"ASC");
				$this->externalData = $this->externalModel->getMonthlyAggregate(365,"ASC");
				break;
				
			default:
				$this->internalData = $this->model->getDayAggregate(30,"ASC");
				$this->externalData = $this->externalModel->getDayAggregate(30,"ASC");
				break;
				
		}
		
	}

	public function mainpage()
	{
		$oTemplate = new Templater('history.html');

		return (string) $oTemplate;
	}

    /**
     * @param Templater $oTemplate
     */
    private function titleHelper(Templater $oTemplate) {
		switch ($this->aParams['range']) {
				
			case 'year':
				$oTemplate->add('title', \Translate\Controller::getDefault()->get('Last year'));
				break;
					
			default:
				$oTemplate->add('title', \Translate\Controller::getDefault()->get('Last 30 days'));
				break;
					
		}
		
	}	
	
	public function tables()
	{
		$oTemplate = new Templater('history-tables.html');
	
		$this->titleHelper($oTemplate);
		
		/*
		 * Get daily aggregate
		*/
		$sTable = '';
		foreach ($this->internalData as $oReadout) {
				
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
		$oTemplate = new Templater('history-charts.html');

		$this->titleHelper($oTemplate);
		
		return (string) $oTemplate;
	}

	/**
	 * render average temperature chart head for google charts
	 * @return string
	 */
	public function chartHead() {

		$t = \Translate\Controller::getDefault();

		$oTemplate = new Templater('history-chartHead.html');

		$oChartDailyPressure = new \General\GoogleChart();
		$oChartDailyPressure->setTitle($t->get('Pressure'));
		$oChartDailyPressure->setDomID('chartDailyPressure');
		$oChartDailyPressure->add('Hour', array());
		$oChartDailyPressure->add('Avg', array());
		$oChartDailyPressure->add('Max', array());
		$oChartDailyPressure->add('Min', array());

		foreach ($this->externalData as $oReadout) {
				
			$oChartDailyPressure->push('Hour', Formater::formatDate($oReadout['Date']));
			$oChartDailyPressure->push('Avg', number_format($oReadout['Pressure'],2,'.',''));
			$oChartDailyPressure->push('Max', number_format($oReadout['MaxPressure'],2,'.',''));
			$oChartDailyPressure->push('Min', number_format($oReadout['MinPressure'],2,'.',''));
				
		}

		$oTemplate->add('chartDailyPressure',$oChartDailyPressure->getHead());

		/*
		 * Day aggregate charts
		*/
		$oChartDailyTemperature = new \General\GoogleChart();
		$oChartDailyTemperature->setTitle($t->get('Temperature'));
		$oChartDailyTemperature->setDomID('chartDailyTemperature');
		$oChartDailyTemperature->add('Day', array());
		$oChartDailyTemperature->add('Avg', array());
		$oChartDailyTemperature->add('Max', array());
		$oChartDailyTemperature->add('Min', array());

		$oChartDailyHumidity = new \General\GoogleChart();
		$oChartDailyHumidity->setTitle($t->get('Humidity'));
		$oChartDailyHumidity->setDomID('chartDailyHumidity');
		$oChartDailyHumidity->add('Day', array());
		$oChartDailyHumidity->add('Avg', array());
		$oChartDailyHumidity->add('Max', array());
		$oChartDailyHumidity->add('Min', array());

		foreach ($this->internalData as $oReadout) {

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