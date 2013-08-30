<?php

namespace Cache;

/**
 * Klasa realizująca cache współdzielony na pliku
 *
 * @author Paweł Spychalski <pawel@spychalski.info>
 * @see http://www.spychalski.info
 * @category Common
 * @version 0.9
 */
class File{

	/**
	 * Tablica wpisów w cache
	 *
	 * @var array
	 */
	private $elements = array ();

	/**
	 * Nazwa pliku przechowującego cache
	 *
	 * @var string
	 */
	private $fileName = null;

	/**
	 * Domyślny czas ważności cache [s]
	 *
	 * @var int
	 */
	private $timeThreshold = 1200;

	/**
	 * Maksymalny rozmiar cache
	 *
	 * @var int
	 */
	private $maxSize = 2000;

	/**
	 * Obecny rozmiar
	 *
	 * @var int
	 */
	private $currentSize = 0;

	/**
	 * Czy zawartość cache zmieniła się po załadowaniu/utworzeniu
	 *
	 * @var boolean
	 */
	private $changed = false;

	/**
	 * Nazwa wpisu w $_SESSION przechowującego następny czas oczyszczania
	 *
	 * @var string
	 */
	private $cacheMaintenanceTimeName = 'CacheOverFileMaintnance';

	/**
	 * Czy dokonuwać kompresji pliku cache
	 *
	 * @var boolean
	 */
	private $useZip = true;

	/**
	 * Obiekt klasy -> Singleton
	 * @var CacheOverFile
	 */
	private static $instance;

	public static function getInstance(){
		if (empty(self::$instance)) {
			$className = __CLASS__;
			self::$instance = new $className;
		}
		return self::$instance;
	}

	/**
	 * Destruktor
	 *
	 */
	public function __destruct() {

		$this->synchronize ();
	}

	/**
	 * konstruktor
	 *
	 * @param int $userID
	 */
	private function __construct() {

		$this->fileName = dirname ( __FILE__ ) . "/../../../userData/" . get_class () . '.sca';

		$this->load ();

	}

	/**
	 * Pobranie cache
	 */
	private function load() {

		try {

			if (file_exists ( $this->fileName )) {
				$tCounter = 0;
				$tFile = fopen ( $this->fileName, 'r' );

				/*
				 * Załóż blokadę na plik cache
				 */
				while ( ! flock ( $tFile, LOCK_SH ) ) {
					usleep ( 5 );
					$tCounter ++;
					if ($tCounter == 100) {
						return false;
					}
				}

				$tContent = fread ( $tFile, filesize ( $this->fileName ) );

				if ($this->useZip) {
					$tContent = gzuncompress ( $tContent );
				}

				$this->elements = unserialize ( $tContent );

				flock ( $tFile, LOCK_UN );
				fclose ( $tFile );

				$tKeys = array_keys ( $this->elements );
				foreach ( $tKeys as $tKey ) {
					$this->maintenace ( $tKey );
				}
					
			}
		} catch ( Exception $e ) {
			psDebug::cThrow ( null, $e, array ('display' => false, 'send' => true ) );
		}
		return true;
	}

	/**
	 * Synchronizacja cache z plikiem
	 *
	 * @return boolean
	 */
	function synchronize() {

		try {
			$tCounter = 0;

			if ($this->changed) {
				$tFile = fopen ( $this->fileName, 'a' );

				/*
				 * Załóż blokadę na plik cache
				 */
				while ( ! flock ( $tFile, LOCK_EX ) ) {
					usleep ( 5 );
					$tCounter ++;
					if ($tCounter == 100) {
						return false;
					}
				}

				/*
				 * Jeśli udało się założyć blokadę, zapisz elementy
				 */
				$tContent = serialize ( $this->elements );

				ftruncate ( $tFile, 0 );

				if ($this->useZip) {
					$tContent = gzcompress ( $tContent );
				}

				fputs ( $tFile, $tContent );

				flock ( $tFile, LOCK_UN );
				fclose ( $tFile );
				return true;
			}
		} catch ( Exception $e ) {
			psDebug::cThrow ( null, $e, array ('display' => false, 'send' => true ) );
			return false;
		}
		return true;
	}

	/**
	 * Oczyszczanie wybranego modułu
	 *
	 * @param string $module
	 * @return boolean
	 */
	private function maintenace($module) {

		if (! isset ( $this->elements [$module] ))
		return false;

		if (! isset ( $_SESSION [$this->cacheMaintenanceTimeName] [$module] )) {
			$_SESSION [$this->cacheMaintenanceTimeName] [$module] = time ();
		}

		//Sprawdz, czy wykonać czyszczenie
		if (time () < $_SESSION [$this->cacheMaintenanceTimeName] [$module])
		return false;
			
		//Ustaw czas następnego czyszczenia
		$_SESSION [$this->cacheMaintenanceTimeName] [$module] = time () + $this->timeThreshold;

		//Pobierz wszystkie klucze w module
		$keys = array_keys ( $this->elements [$module] );

		//Wykonaj pętlę po kluczach
		foreach ( $keys as $value ) {
			//Oczyść przeterminowane klucze
			if (time () > $this->elements [$module] [$value]->getTime ()) {
				unset ( $this->elements [$module] [$value] );
				$this->changed = true;
			}
		}

		return true;

	}

	/**
	 * Sprawdzenie, czy w cache znajduje się wpis
	 *
	 * @param string $module
	 * @param string $property
	 * @return boolean
	 * //TODO allow CacheKey instance passing
	 */
	function check($module, $property) {

		if (isset ( $this->elements [$module] [$property] )) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Pobranie wartości z cache
	 *
	 * @param string $module
	 * @param string $property
	 * @return mixed
	 * //TODO allow CacheKey instance passing
	 */
	function get($module, $property) {

		if (isset ( $this->elements [$module] [$property] )) {
			$tValue = $this->elements [$module] [$property]->getValue ();
			return $tValue;
		} else {
			return NULL;
		}
	}

	/**
	 * Wyczyszczenie konkretnego wpisu w cache
	 *
	 * @param string $module
	 * @param string $property
	 * //TODO allow CacheKey instance passing
	 */
	function clear($module, $property) {

		if (isset ( $this->elements [$module] [$property] )) {
			unset ( $this->elements [$module] [$property] );
			$this->changed = true;
		}

	}

	/**
	 * Wyczyszczenie konkretnego modułu cache
	 *
	 * @param string $module
	 */
	function clearModule($module) {

		if (isset ( $this->elements [$module] )) {
			unset ( $this->elements [$module] );
			$this->changed = true;
		}

	}

	/**
	 * Wstawienei do cache
	 *
	 * @param string $module
	 * @param string $property
	 * @param mixed $value
	 * @param int $sessionLength
	 * //TODO allow CacheKey instance passing
	 */
	function set($module, $property, $value, $sessionLength = null) {

		if ($sessionLength == null) {
			$sessionLength = $this->timeThreshold;
		}

		if (! isset ( $this->elements [$module] [$property] )) {
			$this->elements [$module] [$property] = new FileElement ( $value, time () + $sessionLength );
		} else {
			$this->elements [$module] [$property]->set ( $value, time () + $sessionLength);
		}

		/*
		 * Określ czas następnego czyszczenia cache dla tego modułu
		 */
		if (! isset ( $_SESSION [$this->cacheMaintenanceTimeName] [$module] )) {
			$_SESSION [$this->cacheMaintenanceTimeName] [$module] = time () + $sessionLength;
		}

		$this->changed = true;
	}

	/**
	 * Wyczyszczenie wpisów zależnych od podanej klasy
	 *
	 * @param string $className
	 */
	public function clearClassCache($className) {

		$tKeys = array_keys ( $this->elements );
		foreach ( $tKeys as $tKey ) {
			if (mb_strpos ( $tKey, $className . '::' ) !== false) {
				$this->clearModule ( $tKey );
			}
		}
	}

	/**
	 * Oczyszczenie wszystkich wpisów w cache
	 *
	 */
	private function globalMaintnance() {

		$tKeys = array_keys ( $this->elements );
		foreach ( $tKeys as $tKey ) {
			$this->maintenace ( $tKey );
		}

	}

	/**
	 * Pobranie łącznej liczby modułów
	 */
	public function getCount() {
		return count($this->elements);
	}

	/**
	 * Pobranie łączniej liczby wpisów
	 */
	public function getTotalCount() {

		$retVal = $this->getCount();

		foreach ($this->elements as $tElement) {
			$retVal += count($tElement);
		}

		return $retVal;
	}

	/**
	 * Oczyszczenie całego cache
	 */
	public function clearAll() {
		$this->elements = array();
	}

	public function debug() {
		\psDebug::print_r($this->elements);
	}

}