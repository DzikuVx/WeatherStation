<?php

namespace General;

class Session extends StaticUtils implements \ArrayAccess{

	private static $prefix='frontend_';

	static public function start() {

		$sId = session_id();

		if (empty($sId)) {
			session_start ();
		}
	}

	static public function set($key, $value) {
		$_SESSION[self::$prefix.$key] = $value;
	}

	static public function get($key = null) {

		if (!empty($key)) {

			if (isset($_SESSION[self::$prefix.$key])) {
				return $_SESSION[self::$prefix.$key];
			}
			else {
				return null;
			}

		}
		else {
			return $_SESSION;
		}

	}

	public function offsetSet($offset, $value) {
		self::set($offset, $value);
	}

	public function offsetExists($offset) {
		return isset($_SESSION[self::$prefix.$offset]);
	}

	public function offsetUnset($offset) {
		unset($_SESSION[self::$prefix.$offset]);
	}

	public function offsetGet($offset) {

		if (isset($this->table[$offset])) {
			return $_SESSION[self::$prefix.$offset];
		}else {
			return false;
		}

	}

}