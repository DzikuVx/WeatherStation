<?php

namespace View;

use General\Config;
use General\Formater;
use General\Templater;
use Model\Sensor;
use Translate\Controller as TranslateController;

class History extends Base
{
    /**
     * @var \Model\Sensor
     */
    protected $model = null;

    protected $methodName = 'getDayAggregate';
    protected $period = 30;

    public function __construct(array $aParams)
    {
        parent::__construct($aParams);

        $this->model = new Sensor();

        switch ($aParams['range']) {

            case 'year':
                $this->methodName = 'getMonthlyAggregate';
                $this->period = 365;
                $this->sensorUsageKey = 'history-365';
                $this->timeFormat = 'Y-m';
                break;

            default:
                $this->methodName = 'getDayAggregate';
                $this->period = 30;
                $this->sensorUsageKey = 'history-30';
                $this->timeFormat = 'm-d';
                break;

        }

    }

    public function mainpage()
    {
        $oTemplate = new Templater('history.html');

        return (string)$oTemplate;
    }

    public function tables()
    {
        $oTemplate = new Templater('history-tables.html');

        $this->titleHelper($oTemplate);

        $aggregatedData = array();

        $data = $this->model->{$this->methodName}(Sensor::SENSOR_TEMPERATURE_EXTERNAL, $this->period, 'ASC');
        foreach ($data as $key => $value) {
            $aggregatedData[$value['Date']]['Temperature'] = $value['Avg'];
            $aggregatedData[$value['Date']]['MinTemperature'] = $value['Min'];
            $aggregatedData[$value['Date']]['MaxTemperature'] = $value['Max'];
        }

        $data = $this->model->{$this->methodName}(Sensor::SENSOR_HUMIDITY_EXTERNAL, $this->period, 'ASC');
        foreach ($data as $key => $value) {
            $aggregatedData[$value['Date']]['Humidity'] = $value['Avg'];
            $aggregatedData[$value['Date']]['MinHumidity'] = $value['Min'];
            $aggregatedData[$value['Date']]['MaxHumidity'] = $value['Max'];
        }

        $data = $this->model->{$this->methodName}(Sensor::SENSOR_PRESSURE_EXTERNAL, $this->period, 'ASC');
        foreach ($data as $key => $value) {
            $aggregatedData[$value['Date']]['Pressure'] = $value['Avg'];
            $aggregatedData[$value['Date']]['MinPressure'] = $value['Min'];
            $aggregatedData[$value['Date']]['MaxPressure'] = $value['Max'];
        }

        ksort($aggregatedData);

        /*
         * Get daily aggregate
        */
        $sTable = '';
        foreach ($aggregatedData as $date => $oReadout) {

            $sTable .= '<tr>';
            $sTable .= '<td>' . Formater::formatDate($date) . '</td>';
            $sTable .= '<td>' . Formater::formatFloat($oReadout['MinTemperature'], 2) . '&deg;C</td>';
            $sTable .= '<td>' . Formater::formatFloat($oReadout['Temperature'], 2) . '&deg;C</td>';
            $sTable .= '<td>' . Formater::formatFloat($oReadout['MaxTemperature'], 2) . '&deg;C</td>';
            $sTable .= '<td>' . Formater::formatFloat($oReadout['MinHumidity'], 2) . '%</td>';
            $sTable .= '<td>' . Formater::formatFloat($oReadout['Humidity'], 2) . '%</td>';
            $sTable .= '<td>' . Formater::formatFloat($oReadout['MinPressure'], 2) . 'hPa</td>';
            $sTable .= '<td>' . Formater::formatFloat($oReadout['Pressure'], 2) . 'hPa</td>';
            $sTable .= '<td>' . Formater::formatFloat($oReadout['MaxPressure'], 2) . 'hPa</td>';
            $sTable .= '</tr>';

        }
        $oTemplate->add('DailyTable', $sTable);

        return (string)$oTemplate;
    }

    /**
     * @param Templater $oTemplate
     */
    private function titleHelper(Templater $oTemplate)
    {
        switch ($this->aParams['range']) {

            case 'year':
                $oTemplate->add('title', TranslateController::getDefault()->get('Last year'));
                break;

            default:
                $oTemplate->add('title', TranslateController::getDefault()->get('Last 30 days'));
                break;

        }

    }

    public function charts()
    {
        $oTemplate = new Templater('history-charts.html');
        $this->titleHelper($oTemplate);

        $sensors = Config::getInstance()->get('sensors');

        $sensorContent = '';

        foreach ($sensors as $sensor) {

            if (array_search($this->sensorUsageKey, $sensor['show-in']) === false) {
                continue;
            }

            $sensorTemplate = new Templater('sensor-history.html');

            $sensorTemplate->add('unit', $sensor['unit']);
            $sensorTemplate->add('name', $sensor['name']);
            $sensorTemplate->add('symbol', $sensor['symbol']);

            $sensorContent .= (string)$sensorTemplate;
        }

        $oTemplate->add('sensors-data', $sensorContent);

        return (string)$oTemplate;
    }

}