<?php

namespace PhpCache;

/**
 * Shared cache over file system. 
 * Precaution: rather should not be used. This is rather last resort
 * @author pawel
 *
 */
class File extends AbstractCache {

	/**
	 * @var FileElement[]
	 */
	private $elements = array();

	/**
	 * @var string
	 */
	private $fileName = null;

	/**
	 * @var int
	 */
	private $timeThreshold = 1200;

	/**
	 * @var boolean
	 */
	private $changed = false;

	/**
	 * @var string
	 */
	private $cacheMaintenanceTimeName = 'CacheOverFileMaintenance';

	/**
	 * @var boolean
	 */
	private $useZip = false;

    public static $FILE_PATH = '/cache';

	public function __destruct() {
		$this->synchronize ();
	}

	public function __construct() {

        $dirName = getcwd() . self::$FILE_PATH;

        if (!file_exists($dirName)) {
            mkdir($dirName);
        }

		$this->fileName = $dirName . '/phpcache.sca';
		$this->load ();
	}

	private function load() {

		if (file_exists ( $this->fileName )) {
			$tCounter = 0;
			$tFile = fopen ( $this->fileName, 'r' );

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
				$this->maintenance ( new CacheKey($tKey) );
			}
				
		}
			
		return true;
	}

	/**
	 * Synchronize cache with file
	 *
	 * @return boolean
	 */
	function synchronize() {

		$tCounter = 0;

		if ($this->changed) {
			$tFile = fopen ( $this->fileName, 'a' );

			while ( ! flock ( $tFile, LOCK_EX ) ) {
				usleep ( 5 );
				$tCounter ++;
				if ($tCounter == 100) {
					return false;
				}
			}

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
			
		return true;
	}

	/**
	 * Cache maintenance, remove old entries
	 * @param CacheKey $key
     * @return boolean
	 */
	private function maintenance(CacheKey $key) {

		$module = $key->getModule();
		
		if (! isset ( $this->elements [$module] )) {
			return false;
		}

		if (! isset ( $_SESSION [$this->cacheMaintenanceTimeName] [$module] )) {
			$_SESSION [$this->cacheMaintenanceTimeName] [$module] = time ();
		}

		if (time () < $_SESSION [$this->cacheMaintenanceTimeName] [$module]) {
			return false;
		}
			
		$_SESSION [$this->cacheMaintenanceTimeName] [$module] = time () + $this->timeThreshold;

        /** @noinspection PhpParamsInspection */
        $keys = array_keys($this->elements[$module]);

		foreach ( $keys as $value ) {
            /** @noinspection PhpUndefinedMethodInspection */
            if ($this->elements[$module][$value]->getTime() > time()) {
				unset ( $this->elements [$module] [$value] );
				$this->changed = true;
			}
		}

		return true;
	}

	/**
	 * Check is cache entry is set
	 * @param CacheKey $key
	 * @return boolean
	 */
	function check(CacheKey $key) {

		if (isset ( $this->elements [$key->getModule()] [$key->getProperty()] )) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Get value from cache or null when not set
	 * @param CacheKey $key
	 * @return mixed
	 */
	public function get(CacheKey $key) {

		if (!empty($this->elements[$key->getModule()] [$key->getProperty()])) {
            /** @noinspection PhpUndefinedMethodInspection */
            $tValue = $this->elements[$key->getModule()] [$key->getProperty()]->getValue ();
			return $tValue;
		} else {
			return false;
		}
	}

	/**
	 * Unset cache value
	 * @param CacheKey $key
	 */
	public function clear(CacheKey $key) {
		if (isset ( $this->elements [$key->getModule()] [$key->getProperty()] )) {
			unset ( $this->elements [$key->getModule()] [$key->getProperty()] );
			$this->changed = true;
		}
	}

	/**
	 * Clear whole module and all it's properties
	 * @param CacheKey $key
     * @depreciated
	 */
	function clearModule(CacheKey $key) {

		if (isset ( $this->elements [$key->getModule()] )) {
			unset ( $this->elements [$key->getModule()] );
			$this->changed = true;
		}

	}

	public function set(CacheKey $key, $value, $sessionLength = null) {

		$module 	= $key->getModule();
		$property 	= $key->getProperty();
		
		if ($sessionLength == null) {
			$sessionLength = $this->timeThreshold;
		}

		if (! isset ( $this->elements [$module] [$property] )) {
			$this->elements [$module] [$property] = new FileElement ( $value, time () + $sessionLength );
		} else {
            /** @noinspection PhpUndefinedMethodInspection */
            $this->elements [$module] [$property]->set ( $value, time () + $sessionLength);
		}

		if (! isset ( $_SESSION [$this->cacheMaintenanceTimeName] [$module] )) {
			$_SESSION [$this->cacheMaintenanceTimeName] [$module] = time () + $sessionLength;
		}

		$this->changed = true;
	}

	/**
	 * @param string $className
     * @depreciated
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
	 */
	public function clearAll() {
		$this->elements = array();
	}

}

/**
 * @author Paweł Spychalski <pawel@spychalski.info>
 * @see http://www.spychalski.info
 */
class FileElement{
	/**
	 * @var mixed
	 */
	protected $value = null;

	/**
	 * @var int
	 */
	protected $time = null;

	/**
	 * @return int
	 */
	public function getTime() {

		return $this->time;
	}

	/**
	 * @param int $time
	 */
	public function setTime($time) {
		$this->time = $time;
	}

	/**
	 * @return mixed
	 */
	public function getValue() {

		return $this->value;
	}

	/**
	 * @param mixed $value
	 */
	public function setValue($value) {
		$this->value = $value;
	}

	function __construct($value, $time) {
		$this->value = $value;
		$this->time = $time;
	}

	public function set($value, $time) {
		$this->value = $value;
		$this->time = $time;
	}

}