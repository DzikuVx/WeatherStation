<?php
namespace Controller;

use Exception;
use Model\OpenWeatherMap;

class Api extends Base implements \Interfaces\Singleton {

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

        \General\Environment::setContentJson();
        \General\Environment::set();

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

        $oModel = new \Model\Readout();
        $aRetVal = (array) $oModel->getCurrent();

        $oModel = new OpenWeatherMap();
        $aData = (array) $oModel->getCurrent();

        if ($aData) {
            $aData['ExternalDate'] = $aData['Date'];
            unset($aData['Date']);
            $aRetVal = array_merge($aRetVal, $aData);
        }

    }

}