<?php

namespace Database;

/**
 * MySQL data base interface class
 *
 * @author Pawel Spychalski <pawel@spychalski.info>
 * @link http://www.spychalski.info
 * @version 0.98
 * @copyright 2009 Lynx-IT Pawel Stanislaw Spychalski
 *
 *	@since 2011-02-06 - class uses mysqli instead of mysql extension, persistent connection removed
 */
class MySQLiWrapper {
	protected $dbHandle = null;
	protected $dbConfig;
	protected $queryCount = 0;

	/**
	 * Czy zapisywać zapytania do tabeli statystyk
	 *
	 * @var boolean
	 */
	public $debugMode = false;

	/**
	 * Czy zapisywać każde zapytanie do logu
	 *
	 * @var boolean
	 */
	public $writeToFile = false;

	private $statisticsDb = null;

	public function getStatisticsDb() {
		return $this->statisticsDb;
	}
	
	public function setStatisticsDb($db) {
		$this->statisticsDb = $db;
	}

	protected $logFile = null;

	/**
	 * Commits transaction on current connection
	 * @throws Exception
	 * @since 2011-02-07
	 */
	public function commit() {

		if (! $this->connected) {
			$this->connect ();
		}

		if (!mysqli_commit($this->dbHandle)) {
			throw new Exception( mysqli_error ($this->dbHandle), mysqli_errno ($this->dbHandle) );
		}
	}

	/**
	 * Rollbacks transaction on current connection
	 * @throws Exception
	 * @since 2011-02-07
	 */
	public function rollback() {

		if (! $this->connected) {
			$this->connect ();
		}

		if (!mysqli_rollback($this->dbHandle)) {
			throw new Exception( mysqli_error ($this->dbHandle), mysqli_errno ($this->dbHandle) );
		}
	}

	/**
	 * Enable autocommit for current connection
	 * @throws Exception
	 * @since 2011-02-07
	 */
	public function enableAutocommit() {

		if (! $this->connected) {
			$this->connect ();
		}

		if (!mysqli_autocommit($this->dbHandle, true)) {
			throw new Exception( mysqli_error ($this->dbHandle), mysqli_errno ($this->dbHandle) );
		}

	}

	/**
	 * Disables autocommit for current connection
	 * @throws Exception
	 * @since 2011-02-07
	 */
	public function disableAutocommit() {

		if (! $this->connected) {
			$this->connect ();
		}

		if (!mysqli_autocommit($this->dbHandle, false)) {
			throw new Exception( mysqli_error ($this->dbHandle), mysqli_errno ($this->dbHandle) );
		}

	}

	protected function openLogFile() {
		if (empty ( $this->logFile )) {
			$this->logFile = fopen ( 'db.log', 'a' );
		}
	}

	protected function writeLog($query) {
		$this->openLogFile ();
		fputs ( $this->logFile, $query . "\n" );
	}

	protected function closeLogFile() {
		if (! empty ( $this->logFile )) {
			fclose ( $this->logFile );
		}
	}

	/**
	 * @return int
	 */
	public function getQueryCount() {
		return $this->queryCount;
	}

	/**
	 * Czy nastąpiło połączenie do bazy danych
	 *
	 * @var boolean
	 */
	protected $connected = false;

	/**
	 * Cytowanie stringa do mysqla
	 *
	 * @param string $string
	 * @return string
	 */
	public function quote($string) {

		if (! $this->connected) {
			$this->connect ();
		}

		return mysqli_real_escape_string ( $this->dbHandle, $string );
	}

	/**
	 * Zaquotowanie wszystkich pól
	 * @param array/stdClass $data
	 */
	public function quoteAll(&$data) {

		if (is_array($data)) {

			foreach ($data as $tKey => $tValue) {

				if (is_array($tValue)) {
					foreach($tValue as $tKey2=>$tValue2) {
						$data[$tKey][$tKey2] = $this->quote($tValue2);
					}
				}
				else {
					$data[$tKey] = $this->quote($tValue);
				}
			}

		}elseif(is_object($data)) {

			foreach ($data as $tKey => $tValue) {
				$data->{$tKey} = $this->quote($tValue);
			}

		}

	}

	/**
	 * Zwraca liczbę wierszy zapytania
	 *
	 * @param resource $query
	 * @return int
	 */
	public function count($query) {

		return mysqli_num_rows ( $query );
	}

	/**
	 * Zwraca ID z ostatniego wstawienia
	 *
	 * @return int
	 */
	public function lastUsedID() {

		return mysqli_insert_id ( $this->dbHandle );
	}

	/**
	 * Pobranie liczby wierszy zmodyfikowanych przez ostatnie zapytanie w tej sesji
	 * @return int
	 */
	public function getAffectedRows() {
		return mysqli_affected_rows ( $this->dbHandle );
	}

	/**
	 * Wrapper powtarzający próbę wykonania zapytania w przypadku wystąpienia deadlocka
	 * @param string $query
	 * @param int $delay Czas oczekiwania na kolejne powtórzenia [ms]
	 * @param int $count Maksymalna liczba powtórzeń
	 * @return resource
	 * @since 2011-05-03
	 * @see MySQLiWrapper::execute
	 */
	public function executeAndRetryOnDeadlock($query, $delay = 100, $count = 20) {
		$tResult = null;

		$tCount = 0;

		/*
		 * Nieskończona pętla
		*/
		while(true) {

			try {
				/*
				 * Wykonaj zapytanie
				*/
				$tResult = $this->execute($query);

				/*
				 * jeśli zakończone sukcesem, zwróć wynik
				*/
				return $tResult;

			}catch (Exception $e) {
					
				/*
				 * Pobierz kod błędu
				*/
				switch ($e->getCode()) {

					/*
					 * Jeśli wystąpił 1213, wykonaj pauzę i wykonaj zapytanie ponownie
					*/
					case 1213:
						$tCount++;

						usleep($delay);

						if ($tCount >= $count) {
							throw new Exception('Deadlock query retry count exceeded', 1213);
						}

						break;

					default:
						throw new Exception($e->getMessage(), $e->getCode(), $e->getPrevious());
					break;
				}
					
			}catch (Exception $e) {
				throw new Exception($e->getMessage(), $e->getCode(), $e->getPrevious());
			}

		}

	}

	/**
	 * Zapis danych zapytania do tabel debugu
	 * @param string $query Zapytanie
	 * @param int $tStartTime Czas rozpoczęcia zapytania [microtime]
	 * @param int $tEndTime Czas zakończenia zapytania [microtime]
	 */
	private function saveDebugData($query, $tStartTime, $tEndTime) {
                
		if (empty($this->statisticsDb)) {
			throw new \General\CustomException('Statistics DB not initialized. Use database::setStatisticsDb');
		}

		$tHash = md5 ( $query );
		$tDiff = $tEndTime - $tStartTime;

		$tQuery = "UPDATE st_queries SET Count=Count+1, Time=Time+'{$tDiff}' WHERE Hash='{$tHash}'";
		$this->statisticsDb->execute ( $tQuery );

		if ($this->statisticsDb->getAffectedRows () == 0) {
			$query = str_replace ( "'", '"', $query );
			$tQuery = "INSERT DELAYED INTO st_queries (Hash, Query, Time) VALUES('{$tHash}','{$query}','{$tDiff}') ";
			$this->statisticsDb->execute ( $tQuery );
		}

		$query2 = preg_replace ( '/[0-9]/', '', $query );
		$tHash = md5 ( $query2 );
		$tDiff = $tEndTime - $tStartTime;
		$tQuery = "UPDATE st_parsedqueries SET Count=Count+1, Time=Time+'{$tDiff}' WHERE Hash='{$tHash}'";
		$this->statisticsDb->execute ( $tQuery );

		if ($this->statisticsDb->getAffectedRows () == 0) {
			$query2 = str_replace ( "'", '"', $query2 );
			$tQuery = "INSERT DELAYED INTO st_parsedqueries (Hash, Query, Time) VALUES('{$tHash}','{$query2}','{$tDiff}') ";
			$this->statisticsDb->execute ( $tQuery );
		}

		return true;
	}

	/**
	 * Wykonanie zapytania bazy danych
	 *
	 * @param string $query
	 * @return resource
	 * @throws Exception
	 */
	public function execute($query) {

		if (! $this->connected) {
			$this->connect ();
		}

		if ($this->debugMode) {
			$tStartTime = \General\Formater::getmicrotime ();
		}

		if ($this->dbHandle == null) {
			return false;
		}
		$this->queryCount += 1;

		$tResult = mysqli_query ($this->dbHandle, $query);
		if (! $tResult) {
			throw new Exception ( mysqli_error ($this->dbHandle), mysqli_errno ($this->dbHandle) );
		}

		if ($this->debugMode) {
			$tEndTime = \General\Formater::getmicrotime ();
		}
		/**
		 * logowanie zapytań do pliku
		 */
		if ($this->writeToFile) {
			$this->writeLog ( $query );
		}

		/**
		 * Zapisywanie zapytań do bazy statystyk
		 */
		if ($this->debugMode) {
			$this->saveDebugData($query, $tStartTime, $tEndTime);
		}

		return $tResult;
	}

	/**
	 * Pobranie kolejnych pól z wyniku zapytania
	 *
	 * @param resource $result
	 * @return obiekt zawierający zwracana pola
	 */
	public function fetch($result) {

		if ($this->dbHandle == null)
		return false;
		$tResult = mysqli_fetch_object ( $result );
		return $tResult;
	}
	
	public function fetchAssoc($result) {

		if ($this->dbHandle == null)
		return false;
		$tResult = mysqli_fetch_assoc ( $result );
		return $tResult;
	}

	/**
	 * Wybór bazy danych na serwerze
	 *
	 * @return true
	 */
	public function selectDB() {

		if (! $this->connected) {
			$this->connect ();
		}

		mysqli_select_db ($this->dbHandle, $this->dbConfig ['database'] );
		return true;
	}

	public function connect() {

		try {

			$this->dbHandle = new \mysqli ( $this->dbConfig ['host'], $this->dbConfig ['login'], $this->dbConfig ['password'] );

			if (empty ( $this->dbHandle )) {
				throw new Exception ( 'No connection' );
			}

			$this->connected = true;
			$this->dbConfig ['handle'] = $this->dbHandle;

			//Wybierz bazę danych
			$this->selectDB ();
			mysqli_set_charset($this->dbHandle, "utf8");

		} catch ( Exception $e ) {
			\psDebug::halt ( 'Brak połączenia z bazą danych', $e, array ('display' => false ) );
		}
	}

	/**
	 * Konstruktor bazy danych
	 *
	 * @param Config
	 * @return boolean
	 */
	public function __construct(Config $dbConfig) {

		$this->dbConfig = $dbConfig;
		return true;
	}

	public function __destruct() {

		$this->closeLogFile ();
		$this->close();

	}

	/**
	 * Pobranie uchwytu do bazy danych
	 *
	 * @return resource
	 */
	public function getHandle() {

		if ($this->dbHandle == null)
		return false;
		return $this->dbHandle;
	}

	/**
	 * Zamknięcie połączenia z bazą danych
	 *
	 */
	public function close() {
		if (! empty ( $this->dbHandle )) {
			mysqli_close ( $this->dbHandle );
		}
		return true;
	}

}

class Exception extends \Exception {

}