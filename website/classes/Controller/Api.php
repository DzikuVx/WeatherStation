<?php
namespace Controller;

use Exception;
use General\Environment;
use Interfaces\Singleton;
use Model\Sensor;

class Api extends Base implements Singleton {

    /**
     * @var Api
     */
    private static $instance;

    /**
     * @var array
     */
    private $aParams = array();
	
	private function __construct() {
		$this->aParams = $_REQUEST;
	}

	static public function getInstance() {

		if (empty(self::$instance)) {
			self::$instance = new self();
		}

		if (empty(self::$instance)) {
			throw new \Exception('Main Controller was unable to initiate');
		}

		return self::$instance;
	}

	/**
	 * @return string
	 */
	public function get() {

        Environment::setContentJson();
        Environment::set();

        \Database\Factory::getInstance()->quoteAll($this->aParams);

        $aRetVal = array();

        if (empty ( $this->aParams ['class'] )) {
            $this->aParams ['class'] = 'Api';
        }

        if (empty ( $this->aParams ['method'] )) {
            $this->aParams ['method'] = 'current';
        }

        /*
         * API can call only it's own methods
         */
        $className = '\\Controller\\' . $this->aParams ['class'];

        switch ($this->aParams ['method']) {

            default :
                $methodName = $this->aParams ['method'];
                break;

        }

        if (class_exists($className)) {

            /** @noinspection PhpUndefinedMethodInspection */
            $tObject = $className::getInstance();

            if (method_exists($tObject, $methodName)) {
                $tObject->{$methodName}($this->aParams, $aRetVal);
            }
        }

		return json_encode($aRetVal);
	}

    /**
     * API methods return current condition based on internal readouts as well as OpenWeatherMap
     *
     * @param $aParams
     * @param $aRetVal
     */
    public function current(/** @noinspection PhpUnusedParameterInspection */
        $aParams, &$aRetVal) {

        $model = new Sensor();

        $aRetVal['Temperature'] = $model->getCurrent(Sensor::SENSOR_TEMPERATURE_EXTERNAL);
        $aRetVal['Humidity'] = $model->getCurrent(Sensor::SENSOR_HUMIDITY_EXTERNAL);
        $aRetVal['Pressure'] = $model->getCurrent(Sensor::SENSOR_PRESSURE_EXTERNAL);
        $aRetVal['WindSpeed'] = $model->getCurrent(Sensor::SENSOR_WIND_SPEED_API);
        $aRetVal['WindDirection'] = $model->getCurrent(Sensor::SENSOR_WIND_DIRECTION_API);

        $aRetVal['Date'] = $model->getLastReadoutDate();
        $aRetVal['ExternalDate'] = $model->getLastReadoutDate();

        /*
         * Get additional data
         */
        $proxyFactory = new \Factory\Proxy();

        $oCurrent = $proxyFactory->create('Current')->get();
        $jCurrent = json_decode($oCurrent);

        $aRetVal['WeatherIcon'] = $jCurrent->weather[0]->icon;
        $aRetVal['WeatherCode'] = $jCurrent->weather[0]->id;
        $aRetVal['TempMax'] = $jCurrent->main->temp_max;
        $aRetVal['TempMin'] = $jCurrent->main->temp_min;

        if ($jCurrent->clouds) {
            $aRetVal['Clouds'] = $jCurrent->clouds->all;
        } else {
            $aRetVal['Clouds'] = 0;
        }

        if (isset($jCurrent->rain) && isset($jCurrent->rain->{'3h'})) {
            $aRetVal['Rain'] = $jCurrent->rain->{'3h'};
        } else {
            $aRetVal['Rain'] = 0;
        }

	if (isset($jCurrent->rain) && isset($jCurrent->rain->{'1h'})) {
            $aRetVal['Rain'] = $jCurrent->rain->{'1h'};
        } else {
            $aRetVal['Rain'] = 0;
        }


        if (isset($jCurrent->snow) && isset($jCurrent->snow->{'3h'})) {
            $aRetVal['Snow'] = $jCurrent->snow->{'3h'};
        } else {
            $aRetVal['Snow'] = 0;
        }

	if (isset($jCurrent->snow) && isset($jCurrent->snow->{'1h'})) {
            $aRetVal['Snow'] = $jCurrent->snow->{'1h'};
        } else {
            $aRetVal['Snow'] = 0;
        }


        $oForecast = $proxyFactory->create('Forecast')->get();
        $jForecast = json_decode($oForecast);

        $aData = array();

        foreach ($jForecast->list as $iKey => $oDay) {

            $aData[$iKey]['Date'] = date('Y-m-d', $oDay->dt);
            $aData[$iKey]['WeekDay'] = date('w', $oDay->dt);

            $aData[$iKey]['WeatherIcon'] = $oDay->weather[0]->icon;
            $aData[$iKey]['WeatherCode'] = $oDay->weather[0]->id;

            $aData[$iKey]['TempMax'] = $oDay->temp->max;
            $aData[$iKey]['TempMin'] = $oDay->temp->min;
            $aData[$iKey]['TempDay'] = $oDay->temp->day;
            $aData[$iKey]['TempNight'] = $oDay->temp->night;

            $aData[$iKey]['Clouds'] = $oDay->clouds;
            $aData[$iKey]['Humidity'] = $oDay->humidity;
            $aData[$iKey]['Pressure'] = $oDay->pressure;
            $aData[$iKey]['WindSpeed'] = $oDay->speed;
            $aData[$iKey]['WindDirection'] = $oDay->deg;

            if (isset($oDay->rain)) {
                $aData[$iKey]['Rain'] = $oDay->rain;
            } else {
                $aData[$iKey]['Rain'] = 0;
            }

            if (isset($oDay->snow)) {
                $aData[$iKey]['Snow'] = $oDay->snow;
            } else {
                $aData[$iKey]['Snow'] = 0;
            }

        }

        $aRetVal['Forecast'] = $aData;

    }

}
