<?php

namespace Cache;

/**
 * Klasa cache dla słowników
 * Zapis znajduje się w pamięci, nie jest przechowywany pomiędzy wywołaniami skryptu
 */
class Variable{
	private $cache = array ();
	private $cacheCount = 0;
	private $maxCount = 100;

	private static $instance;

	public function clearAll() {
	}
	
	/**
	 * Konstruktor statyczny
	 */
	public static function getInstance(){
		if (empty(self::$instance)) {
			$className = __CLASS__;
			self::$instance = new $className;
		}
		return self::$instance;
	}

	/**
	 * Konstruktor
	 * @param $maxCount maks długość cache
	 */
	private function __construct() {
	}

	/**
	 * Wstawienie pozycji do cache
	 * @param $module
	 * @param $property
	 * @param $value
	 */
	function set($module, $property, $value) {

		if (! isset ( $this->cache [$module] [$property] )) {
			$this->cacheCount += 1;
		}

		//Wstaw wartość cache
		$this->cache [$module] [$property] = $value;

		return true;
	}

	/**
	 * Sprawdzenie, czy istnieje wpis w cache
	 * @param $module
	 * @param $property
	 * @return boolean
	 */
	function check($module, $property) {

		if (isset ( $this->cache [$module] [$property] )) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Pobranie pozycji z  cache
	 * @param $module
	 * @param $property
	 * @return wartość
	 */
	function get($module, $property) {

		if (isset ( $this->cache [$module] [$property] )) {
			$tTemp = $this->cache [$module] [$property];
			return $tTemp;
		} else {
			return null;
		}
	}

	public function clear($module, $property) {
		return true;
	}

	/**
	 * Wyświetla tablicę cache
	 */
	function debug() {

		\psDebug::print_r ( $this->cache );
	}
}
