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

        $sensors = Config::getInstance()->get('sensors');

        $sensorContent = '';

        foreach ($sensors as $sensor) {

            if (array_search($this->sensorUsageKey, $sensor['show-in']) === false) {
                continue;
            }

            $decimals = $sensor['decimals'];

            $sensorTemplate = new Templater('sensor-table.html');

            $sensorTemplate->add('unit', $sensor['unit']);
            $sensorTemplate->add('name', $sensor['name']);
            $sensorTemplate->add('symbol', $sensor['symbol']);

            $rowPrototype = new Templater('sensor-table-row.html');

            $data = $this->model->{$this->methodName}($sensor['id'], $this->period, 'ASC');

            $rowContent = '';

            foreach ($data as $key => $value) {

                $rowTemplate = clone $rowPrototype;

                $value['Avg'] = Formater::formatFloat($value['Avg'], $decimals);
                $value['Min'] = Formater::formatFloat($value['Min'], $decimals);
                $value['Max'] = Formater::formatFloat($value['Max'], $decimals);

                $rowTemplate->add($value);
                $rowContent .= (string) $rowTemplate;
            }

            $sensorTemplate->add('rows', $rowContent);

            $sensorContent .= (string)$sensorTemplate;
        }

        $oTemplate->add('sensor-data', $sensorContent);

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