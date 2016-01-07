<?php
namespace View;

use General\Config;
use General\GoogleChart;
use General\Templater;
use Interfaces\View;
use Model\Sensor;

abstract class Base implements View {

    protected $methodName = 'getHourAggregate';
    protected $period = 168;

	/**
	 * @var Sensor
	 */
	protected $model = null;

	protected $aParams = null;

    protected $sensorUsageKey;

    protected $timeFormat;

	/**
	 * @param array $aParams
	 */
	public function __construct(array $aParams) {
		$this->aParams = $aParams;
	}
	
	/**
	 * @see Interfaces.View::get()
	 */
	public function get() {
		return '';
	}

    /**
     * render average temperature chart head for google charts
     * @param $settingKey
     * @return string
     */
	public function chartHead($settingKey)
	{
		$oTemplate = new Templater('chartHead.html');

		$sensors = Config::getInstance()->get('sensors');

		$chartContent = '';

		foreach ($sensors as $sensor) {

			if (array_search('overview', $sensor['show-in']) === false) {
				continue;
			}
			$id = $sensor['id'];
			$decimals = $sensor[$settingKey]['decimals'];

			$data = $this->model->{$this->methodName}($id, $this->period, "ASC");

			$chart = new GoogleChart();
			$chart->setTitle('');
			$chart->setDomID('chart' . $sensor['symbol']);

			$chart->add('{T:Time}', array());

			$valuesToDisplay = $sensor[$settingKey]['show'];

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

				$chart->push('{T:Time}', date($this->timeFormat, strtotime($readout['Date'])));
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