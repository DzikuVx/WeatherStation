<?php

namespace Model;

use \Database\Factory as Database;
use phpCache\CacheKey;
use stdClass;

/**
 * @author Paweł
 */
abstract class Base implements \Interfaces\Model {

    /**
     * @var array
     */
    protected $aAssociatedCacheEntries = array();

    protected $tableDateField = '';
    protected $selectList = "";
    protected $tableJoin = "";
    protected $tableName = "";
    protected $tableFields = array();
    protected $extraList = "";

    /**
     * @var array
     */
    protected $aParams = array();

    /**
     * @param array $aParams
     */
    public function __construct(array $aParams = null)
    {
        $this->aParams = $aParams;
    }

}