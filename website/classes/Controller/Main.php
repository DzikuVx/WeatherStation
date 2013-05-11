<?php
namespace Controller;

use General\Config;

use \General\CustomException as CustomException;

/**
 *
 * Główny kontroler aplikacji
 * @author Paweł
 * @brief Główny kontroler aplikacji uruchamiający kontrolery właściwe w zależności żądań użytkownika
 *
 */
class Main extends Base implements \Interfaces\Singleton {

	private static $instance;

	/**
	 * Konstruktor prywatny
	 */
	private function __construct() {

	}

	/**
	 *
	 * Pobranie instancji
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
	 * Główny kontroler
	 * @return string
	 */
	public function get() {
		try {

			/**
			 * Quotowana tablica request
			 * @var array
			 */
			$aRequest = $_REQUEST;
			\Database\Factory::getInstance()->quoteAll($aRequest);

			/**
			 * Inicjacja szablonu
			 * @var \General\Templater
			 */
			$template = new \General\Templater('index.html');

			/*
			 * Rejestracja listenerów
			*/
			\Listeners\Message::getInstance()->register($aRequest, $template);

			if (empty ( $aRequest ['class'] )) {
				$aRequest ['class'] = 'Frontpage';
			}

			if (empty ( $aRequest ['method'] )) {
				$aRequest ['method'] = 'render';
			}

			if (! isset ( $HTTP_RAW_POST_DATA )) {
				$HTTP_RAW_POST_DATA = file_get_contents ( "php://input" );
			}

			$retVal = '';

			$className = '';
			switch ($aRequest ['class']) {

				default:
					$className = '\\Controller\\'.$aRequest ['class'];
					break;
			}


			$methodName = '';
			switch ($aRequest ['method']) {

				default :
					$methodName = $aRequest ['method'];
					break;

			}


			if (class_exists($className)) {

				$tObject = $className::getInstance();

				if (method_exists($tObject, $methodName)) {
					$tObject->{$methodName}($aRequest, $template);
				}
			}

			\Listeners\LowLevelMessage::getInstance()->register($aRequest, $template);
			
		}
		catch ( CustomException $e ) {
			$template->add('mainContent',\General\Debug::cThrow ( $e->getMessage (), $e, array ('send' => false, 'display' => false ) ));
		}
		catch ( Exception $e ) {
			$template->add('mainContent',\General\Debug::cThrow ( null, $e ));
		}

		$template->add('chartHead', '');
		$template->add('listeners', '');
		$template->add('menu', '');
		$template->add('mainContent', '');
		$template->add('titleSecond', '');
		$template->add('pageTitle', '{T:Product Name}');
		$template->add('menu-active-external', '');
		$template->add('menu-active-internal', '');
		
		return (string) $template;

	}

}