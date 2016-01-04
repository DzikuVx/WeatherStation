<?php

namespace Translate;
use PhpCache\CacheKey;
use PhpCache\PhpCache;

/**
 *
 * @author Paweł
 */
class Translate implements \ArrayAccess {

	private $language;
	private $table;

	/**
	 * @var boolean
	 */
	static public $useCache = false;

	/**
	 * @param string $language
	 * @param string $file
	 */
	public function __construct($language, $file = 'translations.php') {
		$this->language = $language;

        $oCache = PhpCache::getInstance()->create();
        $oCacheKey = new CacheKey('translationList', $this->language);

		if (!self::$useCache || !$oCache->check($oCacheKey)) {
            /** @noinspection PhpIncludeInspection */
            require dirname ( __FILE__ ).'/../../translations/'.$file;

            /** @noinspection PhpUndefinedVariableInspection */
            $this->table = $translationTable[$this->language];
			unset ( $translationTable );

			if (self::$useCache) {
                $oCache->set($oCacheKey, $this->table, 86400 );
			}
		} else {
			$this->table = $oCache->get($oCacheKey);
		}

	}

	/**
	 * Pobranie tłumaczenia
	 *
	 * @param string $string
	 * @return string
	 */
	function get($string) {

		if (isset ( $this->table [$string] )) {
			return $this->table [$string];
		} else {
			return $string;
		}
	}

	public function offsetSet($offset, $value) {
		$this->table[$offset] = $value;
	}

	public function offsetExists($offset) {
		return isset($this->table[$offset]);
	}

	public function offsetUnset($offset) {
		unset($this->table[$offset]);
	}

	public function offsetGet($offset) {

		if (isset($this->table[$offset])) {
			return $this->table[$offset];
		}else {
			return false;
		}

	}

}