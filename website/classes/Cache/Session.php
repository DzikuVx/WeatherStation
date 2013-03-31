<?php

namespace Cache;

/**
 * Klasa realizująca keszowanie na bazie mechanizmu sesji
 * @package Common
 * @deprecated
 */
class Session{
	private $table;
	private $size;
	private $currentSize = 0;
	private $timeThreshold = 60;
	private $cacheName = 'cache';
	private $cacheMaintenanceTimeName = 'cacheMaintenanceTime';

	/**
	* Obiekt klasy -> Singleton
	*/
	private static $instance;
	
	/**
	 * Konstruktor statyczny
	 * @deprecated
	 */
	public static function getInstance(){
		if (empty(self::$instance)) {
			$className = __CLASS__;
			self::$instance = new $className;
		}
		return self::$instance;
	}
	
	/**
	 * Czyszczenie cache z przeterminowanych wpisów
	 *
	 * @param string $module
	 * @return boolean
	 */
	private function maintenace($module) {

		if (! isset ( $_SESSION [$this->cacheName] [$module] ))
		return false;
			
		//Sprawdz, czy wykonać czyszczenie
		if (time () < $_SESSION [$this->cacheMaintenanceTimeName] [$module])
		return false;
			
		//Ustaw czas następnego czyszczenia
		$_SESSION [$this->cacheMaintenanceTimeName] [$module] = time () + $this->timeThreshold;

		//Pobierz wszystkie klucze w module
		$keys = array_keys ( $_SESSION [$this->cacheName] [$module] );

		//Wykonaj pętlę po kluczach
		foreach ( $keys as $value ) {
			//Oczyść przeterminowane klucze
			if (time () > $_SESSION [$this->cacheName] [$module] [$value] ['time']) {
				unset ( $_SESSION [$this->cacheName] [$module] [$value] );
			}
		}

		return true;
	}

	/**
	 * @return int
	 */
	public function getTimeThreshold() {
		return $this->timeThreshold;
	}

	/**
	 * @param int $timeThreshold
	 */
	public function setTimeThreshold($timeThreshold) {
		$this->timeThreshold = $timeThreshold;
	}

	/**
	 * Konstruktor
	 *
	 * @param int $size - rozmiar cache
	 * @return boolean
	 */
	private function __construct($size = 100) {
		$this->size = $size;

		return true;
	}

	/**
	 * Sprawdzenie, czy jest wpis w cache
	 *
	 * @param string $module
	 * @param string $id
	 * @return boolean
	 */
	function check($module, $id) {
		if (isset ( $_SESSION [$this->cacheName] [$module] [$id] )) {
			return true;
		} elseif (isset ( $this->table [$module] [$id] )) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Pobranie pozycji z cache
	 *
	 * @param string $module
	 * @param string $id
	 * @return cache
	 */
	function get($module, $id) {
		if (isset ( $_SESSION [$this->cacheName] [$module] [$id] )) {

			$tValue = $_SESSION [$this->cacheName] [$module] [$id] ['value'];

			$this->maintenace ( $module );

			return $tValue;
		} elseif (isset ( $this->table [$module] [$id] )) {
			return $this->table [$module] [$id];
		} else {
			return NULL;
		}
	}

	/**
	 * Wstawienie do cache
	 *
	 * @param string $module
	 * @param string $id
	 * @param string $value
	 * @param boolean $useSession
	 * @return string
	 */
	function set($module, $id, $value, $useSession = false, $expire = null) {
		if ($useSession) {
			//Zapisz w sesji
			if ($expire == null)
			$expire = $this->timeThreshold;
			$_SESSION [$this->cacheName] [$module] [$id] ['value'] = $value;
			$_SESSION [$this->cacheName] [$module] [$id] ['time'] = time () + $expire;

			/*
			 * Określ czas następnego czyszczenia cache dla tego modułu
			 */
			if (! isset ( $_SESSION [$this->cacheMaintenanceTimeName] [$module] )) {
				$_SESSION [$this->cacheMaintenanceTimeName] [$module] = time () + $this->timeThreshold;
			}

		} else {
			//Zapisz w tablicy zwykłej
			$this->table [$module] [$id] = $value;
		}
		$this->currentSize += 1;
		return true;
	}

	/**
	 * Czyszczenie pozycji cache
	 *
	 * @param string $module
	 * @param string $id
	 */
	function clear($module, $id = null) {
		if ($id != null) {
			unset ( $_SESSION [$this->cacheName] [$module] [$id] );
			unset ( $this->table [$module] [$id] );
		} else {
			unset ( $_SESSION [$this->cacheName] [$module] );
			unset ( $this->table [$module] );
		}
	}
}
