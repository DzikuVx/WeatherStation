<?php

namespace Model;

use \Database\Factory as Database;
use phpCache\CacheKey;
use stdClass;

/**
 * @brief Klasa bazowa modeli wykorzystywanych w rozwiązaniu
 * @author Paweł
 */
abstract class Base implements \Interfaces\Model {

    /**
     * Tablica przechowująca listę wspisów w cache z danym obiektem modelu
     * @var array
     */
    protected $aAssociatedCacheEntries = array();

    /**
     * Czy model ma wykorzystywać cache
     * @var boolean
     */
    protected $bUseCache = true;
    protected $tableDateField = '';
    protected $selectList = "";
    protected $tableJoin = "";
    protected $tableList = "";
    protected $tableName = "";
    protected $tableFields = array();
    protected $extraList = "";
    protected $selectCountField = "";
    protected $registryIdField = "";
    protected $registryWhere = '';
    protected $registryOrder = '';

    /**
     * Domyślny kierunek sortowania przy pobraniu całego rejestru
     * @var string
     */
    protected $getAllSorting = null;

    /**
     * @param int $iId
     * @return bool
     */
    public function dropCache($iId = null)
    {

        if (empty($iId)) {
            $iId = $this->aParams['id'];
        }

        if (empty($iId)) {
            return false;
        }
        \phpCache\Factory::getInstance()->create()->clear(get_class($this) . '::loadDataObject', $iId);
        return true;
    }

    /**
     * stdClass będący reprezentacją obiektu bazy danych
     * @var stdClass
     */
    protected $dataObject = null;

    /**
     * Tablica parametrów przekazanych do modelu
     * @var array
     */
    protected $aParams = array();

    /**
     * Pobranie dataObject
     * @return stdClass
     */
    public function getDataObject()
    {
        return $this->dataObject;
    }

    protected function loadDataObjectEncapsulate($val) {
    	
    	return "'".$val."'";
    	
    }
    
    /**
     * Metoda pobiera dane obiektu do właściwości dataObject
     * @throws \Exception
     */
    protected function loadDataObject()
    {

        $oCache = \phpCache\Factory::getInstance()->create();

        if (empty($this->aParams['id'])) {
            throw new \Exception('{T:Nie podano identyfikatora obiektu}');
        }

        $oKey = new CacheKey(get_class($this) . '::loadDataObject', $this->aParams['id']);

        if (!$this->bUseCache || !$oCache->check($oKey)) {

            $sQuery = "SELECT
			{$this->selectList}
				FROM
				$this->tableList
				WHERE
				{$this->registryIdField}={$this->loadDataObjectEncapsulate($this->aParams['id'])}
				LIMIT 1";

            $rQuery = Database::getInstance()->execute($sQuery);
            $this->dataObject = Database::getInstance()->fetch($rQuery);

            $oCache->set($oKey, $this->dataObject);
        }
        else {
            $this->dataObject = $oCache->get($oKey);
        }
    }

    /**
     * Ustawienie dodatkowych warunków dla modelu, wykorzystywane dla rejestrów
     */
    protected function setConstrains()
    {
        
    }

    /**
     * Konstruktor
     * @param array $aParams
     */
    public function __construct(array $aParams = null)
    {
        $this->aParams = $aParams;

        $this->tableList = $this->tableName . ' ' . $this->tableJoin;
        
        $this->setConstrains();

        if (!empty($this->aParams['id'])) {
            $this->loadDataObject();
        }
    }

    /**
     *
     * Pobranie nazwy pola identyfikującego wiersz
     * @return string
     */
    public function getRegistryIdField()
    {
        return $this->registryIdField;
    }

    /**
     * Prztygotowanie warunku zapytania dla rejstru
     * @param string $sSearchIn
     * @param string $sSearchValue
     * @param string $sStartDate
     * @param string $sEndDate
     * @param boolean $bUseDateSelect
     * @return string
     */
    public function prepareWhere($sSearchIn, $sSearchValue, $sStartDate, $sEndDate, $bUseDateSelect = false)
    {

        /*
         * Warunek wyszukiwania w rejestrze
         */
        $set = false;

        if (!empty($this->aParams['constraints'])) {

            $set = true;

            $sCondition = '';

            foreach ($this->aParams['constraints'] as $sKey => $sValue) {

                $sCondition .= " AND {$sKey}='{$sValue}' ";
            }

            if (mb_strlen($sCondition) > 0) {
                $sCondition = mb_substr($sCondition, 4);
            }

            $this->registryWhere .= $sCondition;
        }


        if ($sSearchValue != '') {

            if ($set) {
                $this->registryWhere .= " AND ";
            }

            $this->registryWhere .= $sSearchIn . " LIKE '%" . $sSearchValue . "%'";
            $set = true;
        }

        /*
         * Selektor dat
         */
        if ($bUseDateSelect) {

            if ($set) {
                $this->registryWhere .= " AND ";
            }
            $this->registryWhere .= $this->tableDateField . " >= '" . $sStartDate . "' AND " . $this->tableDateField . " <= '" . $sEndDate . "'";
        }
        
    }

    /**
     * Pobranie pojedynczej strony rejestru
     * @param int $iLimitSkip
     * @param int $iLimitNumber
     * @return resource
     */
    public function getRegistryResults($iLimitSkip, $iLimitNumber)
    {   
        
        $tQuery = "SELECT {$this->selectList} FROM {$this->tableList} WHERE {$this->fixEmptyWhere()} ORDER BY {$this->registryOrder} LIMIT {$iLimitSkip},{$iLimitNumber} ";
 
        return Database::getInstance()->execute($tQuery);
    }

    /**
     * Przygotowanie warunku sortowania do zapytania o wyniki rejestru
     * @param string $sSortBy
     * @param string $sSortDirection
     */
    public function prepareSorting($sSortBy, $sSortDirection)
    {

        $this->registryOrder = $sSortBy . " " . $sSortDirection;
    }

    /**
     *
     * Pobranie liczby wyników w rejstrze spełniających warunki
     * @return int
     */
    public function getRegistryCount()
    {
        $tQuery = Database::getInstance()->execute("SELECT COUNT($this->selectCountField) AS ile FROM {$this->tableList} WHERE {$this->fixEmptyWhere()}");
        while ($tResult = Database::getInstance()->fetch($tQuery)) {
            return $tResult->ile;
        }
        return null;
    }

    /**
     * Naprawienie przypadku gdy warunek WHERE jest pusty
     *
     * @return string
     */
    protected function fixEmptyWhere()
    {

        $sRetVal = "";

        if ($this->extraList == "" && $this->registryWhere == "") {
            $sRetVal = "1";
        }
        elseif ($this->extraList != "" && $this->registryWhere == "") {
            $sRetVal = $this->extraList;
        }
        elseif ($this->extraList == "" && $this->registryWhere != "") {
            $sRetVal = $this->registryWhere;
        }
        elseif ($this->extraList != "" && $this->registryWhere != "") {
            $sRetVal = $this->extraList . " AND " . $this->registryWhere;
        }

        return $sRetVal;
    }

    protected function prepareGetAllQuery()
    {

        $sRetVal = 'SELECT ' . $this->selectList . ' FROM ' . $this->tableList;
        
        if (!empty($this->getAllSorting)) {
            $sRetVal .= ' ORDER BY ' . $this->getAllSorting;
        }

        return $sRetVal;
    }

    /**
     * Funkcja zwracająca tablię wszystkich elementów
     *
     * @return  array Tablica elementów
     * @throws
     * @since 2012-06-18
     * @version 1.0
     */
    public function getAll()
    { 
        $res = Database::getInstance()->execute($this->prepareGetAllQuery());
        $aUsergroup = array();

        while ($oData = Database::getInstance()->fetch($res)) {
            $aUsergroup[] = get_object_vars($oData);
        }
        
        return $aUsergroup;
    }
    
    /**
     * Funkcja przygotowująca zapytanie z WHERE
     *
     * @param   array $aTerms tablica warunków klucz=>wartosc
     * @param   array $aTermsOR tablica warunków "OR" array(kolumna=>array(wartosc1,wartosc2))
     * @return  string zapytanie
     * @throws
     * @since 2012-10-01
     * @version 2.0
     */
    
    protected function prepareGetAllWhereQuery($aTerms,$aTermsOR)
    {

        $sOr = [];
        $sWhere = [];

        $sRetVal = 'SELECT ' . $this->selectList . ' FROM ' . $this->tableList.' WHERE ' ;
        
        if($aTerms!=null){
            foreach ($aTerms as $key => $val){
                $sWhere[]= $key ."=". $val;
            }
        }
        
        if($aTermsOR!=null){
            foreach ($aTermsOR as $column => $values){
               
                   foreach($values as $val){
                       
                       $sOr[]=$column."=".$val;
                   }
               
               $sWhere[]="(".implode($sOr," OR ").")";
            }
            
        }
        
        
        $sRetVal.=implode($sWhere," AND ");
        
        if (!empty($this->getAllSorting)) {
            $sRetVal .= ' ORDER BY ' . $this->getAllSorting;
            
            if (!empty($this->getAllSortingDir)) {
            $sRetVal .= ' ' . $this->getAllSortingDir;
            }
            
        }
       
        return $sRetVal;
    }

    /**
     * Funkcja zwracająca tablię wszystkich elementów z uwzględnieniem warunków WHERE
     *
     * @param array $aTerms tablica warunków klucz=>wartosc
     * @param null $aTermsOR
     * @return array
     */
    public function getAllWhere($aTerms=null,$aTermsOR=null)
    { 
        $res = Database::getInstance()->execute($this->prepareGetAllWhereQuery($aTerms,$aTermsOR));
        $aUsergroup = array();

        while ($oData = Database::getInstance()->fetch($res)) {
            $aUsergroup[] = get_object_vars($oData);
        }
        
        return $aUsergroup;
    }

    /**
     * Funkcja zwracająca tablię wybranych elementów Klucz - Wartość
     *
     * @param string $key Klucz
     * @param string $value Wartość
     * @param bool $addEmpty
     * @return  array Tablica elementów
     * @since 2012-06-18
     * @version 1.0
     */
    public function getAllKeyValue($key, $value, $addEmpty = false)
    {
        $res = Database::getInstance()->execute($this->prepareGetAllQuery());
        $aUsergroup = array();

        if ($addEmpty) {
	        $aUsergroup[''] = '-';
        }
        
        while ($oData = Database::getInstance()->fetch($res)) {
            $aUsergroup[$oData->{$key}] = $oData->{$value};
        }

        return $aUsergroup;
    }

    /**
     * Funkcja usuwająca element o podanym id
     *
     * @param  int $id id elementu do usunięcia
     * @return void
     * @version 1.0
     */
    public function deleteById($id)
    {
        Database::getInstance()->execute('DELETE FROM ' . $this->tableName . ' WHERE ' . $this->registryIdField . ' = ' . $id);
        /*
         * Zrzuć cache
         */
        $this->dropCache($id);
    }

    /**
     * Funkcja usuwająca element o podanych warunkach
     *
     * @param  array $params tablica parametrów
     * @return void
     * @version 1.0
     */
    public function delete($params)
    {
        $aWhere = array();
        foreach ($params as $key => $value) {
            $aWhere[] = '`' . $key . '`' . ' = ' . $value;
        }
        $sWhere = implode(' AND ', $aWhere);
        Database::getInstance()->execute('DELETE FROM ' . $this->tableName . ' WHERE ' . $sWhere);
        $this->dropCache($this->aParams['id']);
    }

    public function checkIfExists($sKey, $mValue = null, $iId = null, $getId = false)
    {
        $sSql = 'SELECT ' . $this->registryIdField . ' FROM ' . $this->tableName . ' WHERE ';

        if (!is_array($sKey)) {
            $aParams = array($sKey => $mValue);
        }
        else {
            $aParams = $sKey;
        }
        $aWhere = array();
        foreach ($aParams as $sKey => $sValue) {
            switch (gettype($sValue)) {
                case 'integer':
                    $aWhere[] = '`' . $sKey . '` = ' . $sValue;
                    break;
                case 'string':
                    $aWhere[] = '`' . $sKey . '` = "' . $sValue . '"';
                    break;
                default:
                    throw new \Exception('Bad value type. Only string and integer accepted');
                    break;
            }
        }
        $sQuery = $sSql . implode(' AND ', $aWhere);

        $res = Database::getInstance()->execute($sQuery);
        $value = Database::getInstance()->fetch($res);

        if (!empty($value)) {
            if (!empty($iId)) {
                if ($value->{$this->registryIdField} == $iId) {
                    return true;
                }
                else {
                    return false;
                }
            }
            if ($getId) {
                return $value->{$this->registryIdField};
            }
            else {
                return true;
            }
        }
        else {
            if (!empty($iId)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Metoda dodaje nowy rekord do bazy.
     *
     * @param   array   $aParams    Tablica z danymi do dodania do bazy
     * @return  int     Identyfikator ostatnio dodanego rekordu
     * @throws
     * @since   2012-06-18
     * @version 1.0
     */
    public function add($aParams)
    {
        $aSet = array();
        $aValues = array();

        if (empty($this->tableFields)) {
            throw new \Exception('Brak tableFields dla modelu');
        }

        foreach ($aParams as $sKey => $mValue) {
            if ($mValue === null) {
                continue;
            }
            if (in_array($sKey, $this->tableFields)) {
                $aSet[] = '`' . $sKey . '`';
                switch (gettype($mValue)) {
                    case 'integer':
                    case 'boolean':
                        $aValues[] = $mValue;
                        break;
                    case 'string':
                        $aValues[] = "'$mValue'";
                }
            }
        }

        $sSet = implode(',', $aSet);
        $sValues = implode(',', $aValues);

        $sSql = 'INSERT INTO ' . $this->tableName . ' (' . $sSet . ') VALUES (' . $sValues . ')';
        $oDatabase = Database::getInstance();
        $oDatabase->execute($sSql);
        $iLastInsertId = $oDatabase->lastUsedID();

        return $iLastInsertId;
    }

    /**
     * Metoda edytuje dane w tabeli.
     *
     * @param   array $aParams Tablica z danymi do edycji bazy
     * @param $iId
     * @internal param $int $$iId       Identyfikator rekordu
     * @return  int     Identyfikator ostatnio zedytowanego rekordu
     * @since   2012-06-19
     * @version 1.0
     */
    public function edit($aParams, $iId)
    {
        $aUpdate = array();

        foreach ($aParams as $sKey => $mValue) {
            if ($mValue === null) {
                continue;
            }
            if (in_array($sKey, $this->tableFields)) {
                switch (gettype($mValue)) {
                    case 'integer':
                    case 'boolean':
                        $aUpdate[$sKey] = '`' . $sKey . '`' . '=' . $mValue;
                        break;
                    case 'string':
                        $aUpdate[$sKey] = '`' . $sKey . '`' . '=' . "'$mValue'";
                }
            }
        }

        $sUpdate = implode(',', $aUpdate);

        $sSql = 'UPDATE ' . $this->tableName . ' SET ' . $sUpdate .
                ' WHERE ' . $this->registryIdField . '=' . $iId;
        $oDatabase = Database::getInstance();
        $oDatabase->execute($sSql);
        $iLastInsertId = $oDatabase->lastUsedID();

        /*
         * Zrzuć cache
         */
        $this->dropCache($iId);

        return $iLastInsertId;
    }

}