<?php
namespace Controller;

use Exception;
use General\Environment;
use General\Session;
use General\Templater;
use Interfaces\Singleton;
use Listeners\LowLevelMessage;
use Listeners\Message;
use psDebug\CustomException;
use psDebug\Debug;

class Main extends Base implements Singleton {

	private static $instance;

    /**
     * @var array
     */
    private $aParams = array();
	
	private function __construct() {
		$this->aParams = $_REQUEST;
	}

	/**
	 * @throws \Exception
	 */
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

        Environment::setContentHtml();
        Session::start();
        Environment::set();

        /**
         * @var Templater
         */
        $template = new Templater('index.html');

		try {

			\Database\Factory::getInstance()->quoteAll($this->aParams);

			Message::getInstance()->register($this->aParams, $template);

			if (empty ( $this->aParams ['class'] )) {
				$this->aParams ['class'] = 'Overview';
			}

			if (empty ( $this->aParams ['method'] )) {
				$this->aParams ['method'] = 'render';
			}

			switch ($this->aParams ['class']) {

				default:
					$className = '\\Controller\\'.$this->aParams ['class'];
					break;
			}

			switch ($this->aParams ['method']) {

				default :
					$methodName = $this->aParams ['method'];
					break;

			}

			if (class_exists($className)) {

                /** @noinspection PhpUndefinedMethodInspection */
                $tObject = $className::getInstance();

				if (method_exists($tObject, $methodName)) {
					$tObject->{$methodName}($this->aParams, $template);
				}
			}

			LowLevelMessage::getInstance()->register($this->aParams, $template);
			
		}
		catch ( CustomException $e ) {
			error_log($e->getMessage());
			$template->add('mainContent', Debug::cThrow ( $e->getMessage (), $e, array ('send' => false, 'display' => false ) ));
		}
		catch ( Exception $e ) {
			error_log($e->getMessage());
			$template->add('mainContent', Debug::cThrow ( null, $e ));
		}

		$template->add('chartHead', '');
		$template->add('listeners', '');
		$template->add('menu', '');
		$template->add('mainContent', '');
		$template->add('titleSecond', '');
		$template->add('pageTitle', '{T:Product Name}');
		
		$sHtml = (string) $template;
		
		/*
		 * Remove all menu-active-* occurences
		 */
		$sHtml = preg_replace('!({submenu-active-[^}]*})!', '', $sHtml);
		$sHtml = preg_replace('!({menu-active-[^}]*})!', '', $sHtml);
		
		/*
		 * Add params
		 */
		$sHtml = preg_replace_callback('!({params:[^}]*})!', array($this, 'paramsInjecter'), $sHtml);
		
		
		return $sHtml;

	}

	private function paramsInjecter($matches)
	{
	
		$retVal = $matches [1];
		$retVal = mb_substr($retVal, 8, - 1);
	
		if (isset($this->aParams[$retVal])) {
			$retVal = $this->aParams[$retVal];
		}else {
			$retVal = '';
		}
		
		return $retVal;
	}
	
}