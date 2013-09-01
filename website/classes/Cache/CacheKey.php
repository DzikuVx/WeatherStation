<?php

namespace Cache;

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
	public function __construct($module, $property) {

		if (is_object($module)) {
			$this->module = get_class($module);
		}else {
			$this->module = (string) $module;
		}

		$this->property = (string) $property;

	}

	public function setModule($value) {
		$this->module = $value;
	}

	public function setProperty($value) {
		$this->property = $value;
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