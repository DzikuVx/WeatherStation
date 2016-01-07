<?php

namespace View;

use Factory\Proxy as FactoryProxy;
use General\Config;
use General\Formater;
use General\Templater;
use Model\Sensor;

class Overview extends Base
{

    protected $methodName = 'getHourAggregate';
    protected $period = 168;
    protected $sensorUsageKey = 'overview';
    protected $timeFormat = 'H:i';

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

            if (array_search($this->sensorUsageKey, $sensor['show-in']) === false) {
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

}