<?php

namespace View;

use Factory\Proxy as FactoryProxy;
use General\Config;
use General\Formater;
use General\GoogleChart;
use General\Templater;
use Model\Sensor;

class Overview extends Base
{

    /**
     * @var Sensor
     */
    protected $model = null;

    public function __construct(array $aParams)
    {
        parent::__construct($aParams);

        $this->model = new Sensor();
    }

    public function mainpage()
    {
        $oTemplate = new Templater('overview.html');

        $oCurrent = new \stdClass();
        $oCurrent->Temperature = Formater::formatFloat($this->model->getCurrent(Sensor::SENSOR_TEMPERATURE_EXTERNAL), 0);
        $oCurrent->Humidity = Formater::formatFloat($this->model->getCurrent(Sensor::SENSOR_HUMIDITY_EXTERNAL), 0);
        $oCurrent->Pressure = Formater::formatFloat($this->model->getCurrent(Sensor::SENSOR_PRESSURE_EXTERNAL), 0);
        $oCurrent->WindSpeed = Formater::formatFloat($this->model->getCurrent(Sensor::SENSOR_WIND_SPEED_API), 0);
        $oCurrent->WindDirection = Formater::formatFloat(round($this->model->getCurrent(Sensor::SENSOR_WIND_DIRECTION_API) / 10) * 10, 0);

        $oTemplate->add($oCurrent);
        $oTemplate->add('LastReadout', $this->model->getLastReadoutDate());

        $sensors = Config::getInstance()->get('sensors');

        $sensorContent = '';

        foreach ($sensors as $sensor) {

            if (array_search('overview', $sensor['show-in']) === false) {
                continue;
            }

            $sensorTemplate = new Templater('sensor-overview.html');

            $id = $sensor['id'];
            $decimals = $sensor['decimals'];

            $sensorTemplate->add('unit', $sensor['unit']);
            $sensorTemplate->add('name', $sensor['name']);
            $sensorTemplate->add('symbol', $sensor['symbol']);

            $sensorTemplate->add('1dAvg', Formater::formatFloat($this->model->getAverage($id, 1), $decimals));
            $sensorTemplate->add('1dMin', Formater::formatFloat($this->model->getMin($id, 1), $decimals));
            $sensorTemplate->add('1dMax', Formater::formatFloat($this->model->getMax($id, 1), $decimals));

            $sensorTemplate->add('7dAvg', Formater::formatFloat($this->model->getAverage($id, 7), $decimals));
            $sensorTemplate->add('7dMin', Formater::formatFloat($this->model->getMin($id, 7), $decimals));
            $sensorTemplate->add('7dMax', Formater::formatFloat($this->model->getMax($id, 7), $decimals));

            $sensorContent .= (string)$sensorTemplate;
        }

        $oTemplate->add('sensors', $sensorContent);

        $proxyFactory = new FactoryProxy();

        $oTemplate->add('proxyCurrent', $proxyFactory->create('Current')->get());
        $oTemplate->add('proxyForecast', $proxyFactory->create('Forecast')->get());

        return (string)$oTemplate;
    }

    /**
     * render average temperature chart head for google charts
     * @return string
     */
    public function chartHead()
    {
        $oTemplate = new Templater('chartHead.html');

        $sensors = Config::getInstance()->get('sensors');

        $chartContent = '';

        foreach ($sensors as $sensor) {

            if (array_search('overview', $sensor['show-in']) === false) {
                continue;
            }
            $id = $sensor['id'];
            $decimals = $sensor['graph-overview-setting']['decimals'];

            $data = $this->model->getHourAggregate($id, 168, "ASC");

            $chart = new GoogleChart();
            $chart->setTitle('');
            $chart->setDomID('chartHour' . $sensor['symbol']);

            $chart->add('{T:Time}', array());

            $valuesToDisplay = $sensor['graph-overview-setting']['show'];

            $doMin = false;
            $doAvg = false;
            $doMax = false;

            if (array_search('avg', $valuesToDisplay) !== false) {
                $chart->add('{T:Avg}', array());
                $doAvg = true;
            }

            if (array_search('min', $valuesToDisplay) !== false) {
                $chart->add('{T:Min}', array());
                $doMin = true;
            }

            if (array_search('max', $valuesToDisplay) !== false) {
                $chart->add('{T:Max}', array());
                $doMax = true;
            }

            foreach ($data as $readout) {

                $chart->push('{T:Time}', Formater::formatTime($readout['Date']));
                if ($doMin) {
                    $chart->push('{T:Min}', number_format($readout['Min'], $decimals, '.', ''));
                }
                if ($doAvg) {
                    $chart->push('{T:Avg}', number_format($readout['Avg'], $decimals, '.', ''));
                }
                if ($doMax) {
                    $chart->push('{T:Max}', number_format($readout['Max'], $decimals, '.', ''));
                }
            }

            $chartContent .= $chart->getHead();
        }

        $oTemplate->add('chart-data', $chartContent);

        return (string)$oTemplate;
    }

}