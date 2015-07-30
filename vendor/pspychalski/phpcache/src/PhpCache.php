<?php

namespace PhpCache;

/**
 * Cache method factory
 * Static
 * @author Paweł
 */
class PhpCache {

	/**
	 * Name of default caching mechanism
	 * @var string
	 */
	public static $sDefaultMechanism = 'Variable';
	
	/**
	 * Array of registered and available caching mechanisms
	 * @var array
	 */
	private $aRegisteredMechanisms = array('Apc', 'File', 'Memcached', 'Session', 'Variable', 'Redis');
	
	/**
	 * Array of caching method objects
	 * @var array
	 */
	private $aCacheInstance = array();

	private static $instance;
	
	/**
	 * Private constructor
	 */
	private function __construct() {

	}

    /**
     * Private __clone magic method
     */
    private function __clone() {

    }

	/**
	 * Create and return caching mechanism object according to passed name
	 * @param string $sMethod
	 * @return Apc,File,Memcached,Session,Variable
     * @throws Exception
	 */
	public function create($sMethod = null) {
		
		/*
		 * If no method passed, use default
		 */
		if (empty($sMethod)) {
			$sMethod = self::$sDefaultMechanism;
		}

		/*
		 * check if passed name is an registered method
		 */
		if (array_search($sMethod, $this->aRegisteredMechanisms) === false) {
			throw new Exception('Unknown caching mechanism');
		}
		
		/*
		 * If caching mechanism not initialised, create new
		 */
		if (!isset($this->aCacheInstance[$sMethod])) {

            /** @noinspection PhpIncludeInspection */
            require_once dirname ( __FILE__ ) . '/' . $sMethod . '.php';
			
			$sClassName = '\phpCache\\' . $sMethod;

            /** @noinspection PhpUndefinedMethodInspection */
            $this->aCacheInstance[$sMethod] = new $sClassName();
			
		} 
		
		return $this->aCacheInstance[$sMethod];
	}

	/**
	 * get factory instance
	 * @return PhpCache
	 */
	static public function getInstance() {

		if (empty(self::$instance)) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}

/**
 * 
 * Class providing caching key functionality
 * 
 * @author pawel
 *
 */
class CacheKey {

	/**
	 * @var string
	 */
	private $module = '';

	/**
	 * @var string
	 */
	private $property = '';

	/**
	 * @param mixed $module
	 * @param string $property
	 */
	public function __construct($module, $property = null) {
		$this->setModule($module);
		$this->setProperty($property);
	}

	/**
	 * Set module property
	 * @param mixed $value
	 */
	public function setModule($value) {
		if (is_object($value)) {
			$this->module = get_class($value);
		}else {
			$this->module = (string) $value;
		}
	}

	/**
	 * Set value property
	 * @param string $value
	 */
	public function setProperty($value) {
		$this->property = (string) $value;
	}

	/**
	 * @return string
	 */
	public function getModule() {
		return $this->module;
	}

	/**
	 * @return string
	 */
	public function getProperty() {
		return $this->property;
	}
}

class Exception extends \Exception {

}