<?php

namespace Translate;

class Controller {

	private static $data = array();

	private static $defaultLanguage = 'en';
	private static $file = 'translations.php';
	
	private function __construct() {
		
	}
	
	public static function get($language) {
		
		if (!isset(self::$data[$language])) {
			self::connect($language);
		}
		
		return self::$data[$language];
	}
	
	/**
	 * @return Translate
	 */
	public static function getDefault() {

		return self::get(self::$defaultLanguage);
	}

	private static function connect($language) {
		self::$data[$language] = new Translate($language, self::$file);
	}
	
	public static function setDefaultLanguage($language) {
		self::$defaultLanguage = $language;
	}
	
	public static function setFile($file) {
		self::$file = $file;
	}
	
}