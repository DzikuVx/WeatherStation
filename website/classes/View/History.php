<?php

namespace View;

use General\Formater;
use General\GoogleChart;
use General\Templater;
use Model\Sensor;
use Translate\Controller as TranslateController;

class History extends Base
{

    /**
     * @var \Model\Sensor
     */
    protected $model = null;


//	protected $internalData = array();
//	protected $externalData = array();

    private $methodName = 'getDayAggregate';
    private $period = 30;

    public function __construct(array $aParams)
    {
        parent::__construct($aParams);

        $this->model = new Sensor();

        switch ($aParams['range']) {

            case 'year':
                $this->methodName = 'getMonthlyAggregate';
                $this->period = 365;
                break;

            default:
                $this->methodName = 'getDayAggregate';
                $this->period = 30;
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

        return (string)$oTemplate;
    }

    /**
     * render average temperature chart head for google charts
     * @return string
     */
    public function chartHead()
    {

        $t = TranslateController::getDefault();

        $oTemplate = new Templater('history-chartHead.html');

        $oTemplate->add('chartDailyPressure', $this->renderSensorChart(
            new GoogleChart(),
            $this->model->{$this->methodName}(Sensor::SENSOR_PRESSURE_EXTERNAL, $this->period, 'ASC'),
            $t->get('Pressure'),
            'chartDailyPressure',
            true));

        $oTemplate->add('chartDailyTemperature', $this->renderSensorChart(
            new GoogleChart(),
            $this->model->{$this->methodName}(Sensor::SENSOR_TEMPERATURE_EXTERNAL, $this->period, 'ASC'),
            $t->get('Temperature'),
            'chartDailyTemperature',
            true));

        $oTemplate->add('chartDailyHumidity', $this->renderSensorChart(
            new GoogleChart(),
            $this->model->{$this->methodName}(Sensor::SENSOR_HUMIDITY_EXTERNAL, $this->period, 'ASC'),
            $t->get('Humidity'),
            'chartDailyHumidity',
            false));

        return (string)$oTemplate;

    }

    /**
     * @param GoogleChart $chart
     * @param $data
     * @param $title
     * @param $domId
     * @param bool $withMax
     * @param bool $withMin
     * @param int $decimals
     * @return string
     */
    private function renderSensorChart($chart, $data, $title, $domId, $withMax = true, $withMin = true, $decimals = 2)
    {
        $chart->setTitle($title);
        $chart->setDomID($domId);
        $chart->add('Hour', array());
        $chart->add('Avg', array());
        if ($withMax) {
            $chart->add('Max', array());
        }
        if ($withMin) {
            $chart->add('Min', array());
        }

        foreach ($data as $oReadout) {
            $chart->push('Hour', Formater::formatDate($oReadout['Date']));
            $chart->push('Avg', number_format($oReadout['Avg'], $decimals, '.', ''));
            if ($withMax) {
                $chart->push('Max', number_format($oReadout['Max'], $decimals, '.', ''));
            }
            if ($withMin) {
                $chart->push('Min', number_format($oReadout['Min'], $decimals, '.', ''));
            }
        }

        return $chart->getHead();
    }

}