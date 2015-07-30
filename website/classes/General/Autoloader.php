<?php
namespace General;

/**
 * Universal PHP class loader with basic Namespace support
 * @author Paweł
 * @since 2012-04-17
 *
 */
class Autoloader {

	static public function loadClass($sClassName) {

		$sNamespaced = str_replace('\\', '/', $sClassName);

		$sBaseDir = dirname ( __FILE__ ).'/..';

		/*
		 * Autolodaer with Namespaces feature
		*/
		if (file_exists($sBaseDir.'/' . $sNamespaced . '.php')) {
			require_once $sBaseDir.'/' . $sNamespaced . '.php';
		}
		elseif (file_exists ( $sBaseDir.'/' . $sClassName . '.php' )) {
			/**
			 * Klasy common
			 */

			require_once $sBaseDir.'/' . $sClassName . '.php';
		}

	}

	static function register() {
		spl_autoload_register("General\Autoloader::loadClass");
	}
	
	static function unregister() {
		spl_autoload_unregister("General\Autoloader::loadClass");
	}

}