<?php

namespace View;

use General\Formater;
use General\GoogleChart;
use General\Templater;
use Model\Sensor;
use Factory\Proxy as FactoryProxy;

class Overview extends Base {

	/**
	 * @var Sensor
	 */
	protected $model = null;
	
	public function __construct(array $aParams) {
		parent::__construct($aParams);
		
		$this->model = new Sensor();
	}
	
	public function mainpage()
	{
		$oTemplate = new Templater('overview.html');

		$oCurrent = new \stdClass();
        $oCurrent->Temperature = Formater::formatFloat($this->model->getCurrent(Sensor::SENSOR_TEMPERATURE_EXTERNAL), 0);
        $oCurrent->Humidity = Formater::formatFloat($this->model->getCurrent(Sensor::SENSOR_HUMIDITY_EXTERNAL), 0);

        $oTemplate->add($oCurrent);
        $oTemplate->add('LastReadout', $this->model->getLastReadoutDate());

		$oTemplate->add('1dTempAvg', Formater::formatFloat($this->model->getAverage(Sensor::SENSOR_TEMPERATURE_EXTERNAL, 1), 2));
		$oTemplate->add('1dHumidityAvg', Formater::formatFloat($this->model->getAverage(Sensor::SENSOR_HUMIDITY_EXTERNAL, 1), 2));
		
		$oTemplate->add('1dTempMin', Formater::formatFloat($this->model->getMin(Sensor::SENSOR_TEMPERATURE_EXTERNAL, 1), 2));
		$oTemplate->add('1dHumidityMin', Formater::formatFloat($this->model->getMin(Sensor::SENSOR_HUMIDITY_EXTERNAL, 1), 2));
		
		$oTemplate->add('1dTempMax', Formater::formatFloat($this->model->getMax(Sensor::SENSOR_TEMPERATURE_EXTERNAL, 1), 2));
		$oTemplate->add('1dHumidityMax', Formater::formatFloat($this->model->getMax(Sensor::SENSOR_HUMIDITY_EXTERNAL, 1), 2));
		
		$oTemplate->add('7dTempAvg', Formater::formatFloat($this->model->getAverage(Sensor::SENSOR_TEMPERATURE_EXTERNAL, 7), 2));
		$oTemplate->add('7dHumidityAvg', Formater::formatFloat($this->model->getAverage(Sensor::SENSOR_HUMIDITY_EXTERNAL, 7), 2));
		
		$oTemplate->add('7dTempMin', Formater::formatFloat($this->model->getMin(Sensor::SENSOR_TEMPERATURE_EXTERNAL, 7), 2));
		$oTemplate->add('7dHumidityMin', Formater::formatFloat($this->model->getMin(Sensor::SENSOR_HUMIDITY_EXTERNAL, 7), 2));
		
		$oTemplate->add('7dTempMax', Formater::formatFloat($this->model->getMax(Sensor::SENSOR_TEMPERATURE_EXTERNAL, 7), 2));
		$oTemplate->add('7dHumidityMax', Formater::formatFloat($this->model->getMax(Sensor::SENSOR_HUMIDITY_EXTERNAL, 7), 2));

        /*
         * External data from Open Weather Map
         */
		$oCurrent = new \stdClass();

        $oCurrent->Pressure = Formater::formatFloat($this->model->getCurrent(Sensor::SENSOR_PRESSURE_EXTERNAL), 0);
        $oCurrent->WindSpeed = Formater::formatFloat($this->model->getCurrent(Sensor::SENSOR_WIND_SPEED_API), 0);
        $oCurrent->WindDirection = Formater::formatFloat(round($this->model->getCurrent(Sensor::SENSOR_WIND_DIRECTION_API) / 10) * 10, 0);

		$oTemplate->add($oCurrent);
		
		$oTemplate->add('1dPressureAvg', Formater::formatFloat($this->model->getAverage(Sensor::SENSOR_PRESSURE_EXTERNAL, 1), 2));
		$oTemplate->add('1dWindAvg', Formater::formatFloat($this->model->getAverage(Sensor::SENSOR_WIND_SPEED_API, 1), 2));
		
		$oTemplate->add('1dPressureMin', Formater::formatFloat($this->model->getMin(Sensor::SENSOR_PRESSURE_EXTERNAL, 1), 2));
		$oTemplate->add('1dWindMin', Formater::formatFloat($this->model->getMin(Sensor::SENSOR_WIND_SPEED_API, 1), 2));
		
		$oTemplate->add('1dPressureMax', Formater::formatFloat($this->model->getMax(Sensor::SENSOR_PRESSURE_EXTERNAL, 1), 2));
		$oTemplate->add('1dWindMax', Formater::formatFloat($this->model->getMax(Sensor::SENSOR_WIND_SPEED_API, 1), 2));
		
		$oTemplate->add('7dPressureAvg', Formater::formatFloat($this->model->getAverage(Sensor::SENSOR_PRESSURE_EXTERNAL, 7), 2));
		$oTemplate->add('7dWindAvg', Formater::formatFloat($this->model->getAverage(Sensor::SENSOR_WIND_SPEED_API, 7), 2));
		
		$oTemplate->add('7dPressureMin', Formater::formatFloat($this->model->getMin(Sensor::SENSOR_PRESSURE_EXTERNAL, 7), 2));
		$oTemplate->add('7dWindMin', Formater::formatFloat($this->model->getMin(Sensor::SENSOR_WIND_SPEED_API, 7), 2));
		
		$oTemplate->add('7dPressureMax', Formater::formatFloat($this->model->getMax(Sensor::SENSOR_PRESSURE_EXTERNAL, 7), 2));
		$oTemplate->add('7dWindMax', Formater::formatFloat($this->model->getMax(Sensor::SENSOR_WIND_SPEED_API, 7), 2));
		
		$proxyFactory = new FactoryProxy();
		
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


		/** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
		$t = \Translate\Controller::getDefault();

        $oTemplate = new Templater('chartHead.html');

        /**
		 * Data from OpenWeatherMap.org
		 */
		$aHistory = $this->model->getHourAggregate(Sensor::SENSOR_PRESSURE_EXTERNAL, 168, "ASC");

		$oChartHourPressure = new GoogleChart();
		$oChartHourPressure->setTitle($t->get('Pressure') . " [hPa]");
		$oChartHourPressure->setDomID('chartHourPressure');
		$oChartHourPressure->add('Hour', array());
		$oChartHourPressure->add('Min', array());
		
		foreach ($aHistory as $oReadout) {
			
			$oChartHourPressure->push('Hour', Formater::formatTime($oReadout['Date']));
			$oChartHourPressure->push('Min', number_format($oReadout['Min'],2,'.',''));
			
		}
		$oTemplate->add('chartHourPressure',$oChartHourPressure->getHead());

        /* Wind speed chart */

        $aHistory = $this->model->getHourAggregate(Sensor::SENSOR_WIND_SPEED_API, 168, "ASC");

        $oChartHourWindSpeed = new GoogleChart();
        $oChartHourWindSpeed->setTitle($t->get('Wind speed') . " [m/s]");
        $oChartHourWindSpeed->setDomID('chartHourWindSpeed');
        $oChartHourWindSpeed->add('Hour', array());
        $oChartHourWindSpeed->add('Min', array());

        foreach ($aHistory as $oReadout) {

            $oChartHourWindSpeed->push('Hour', Formater::formatTime($oReadout['Date']));
            $oChartHourWindSpeed->push('Min', number_format($oReadout['Min'],2,'.',''));

        }
        $oTemplate->add('chartHourWindSpeed',$oChartHourWindSpeed->getHead());

		/*
		 * Hour Aggregate charts
		 */
		$aHistory = $this->model->getHourAggregate(Sensor::SENSOR_TEMPERATURE_EXTERNAL, 168,"ASC");
		
		$oChartHourTemperature = new GoogleChart();
		$oChartHourTemperature->setTitle($t->get('Temperature') . " [C]");
		$oChartHourTemperature->setDomID('chartHourTemperature');
		$oChartHourTemperature->add('Hour', array());
		$oChartHourTemperature->add('Avg', array());
		
		foreach ($aHistory as $oReadout) {
			
			$oChartHourTemperature->push('Hour', Formater::formatTime($oReadout['Date']));
			$oChartHourTemperature->push('Avg', number_format($oReadout['Avg'],2));
			
		}
		$oTemplate->add('chartHourTemperature',$oChartHourTemperature->getHead());

        $aHistory = $this->model->getHourAggregate(Sensor::SENSOR_HUMIDITY_EXTERNAL, 168,"ASC");

        $oChartHourHumidity = new GoogleChart();
        $oChartHourHumidity->setTitle($t->get('Humidity') . " [%]");
        $oChartHourHumidity->setDomID('chartHourHumidity');
        $oChartHourHumidity->add('Hour', array());
        $oChartHourHumidity->add('Avg', array());

        foreach ($aHistory as $oReadout) {

            $oChartHourHumidity->push('Hour', Formater::formatTime($oReadout['Date']));
            $oChartHourHumidity->push('Avg', number_format($oReadout['Avg'],2));

        }

        $oTemplate->add('chartHourHumidity',$oChartHourHumidity->getHead());

		return (string) $oTemplate;
	}
	
}