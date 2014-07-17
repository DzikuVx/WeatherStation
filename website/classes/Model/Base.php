<?php

namespace Model;

use \Database\Factory as Database;
use Database\SQLiteWrapper;
use phpCache\Apc;
use phpCache\CacheKey;
use stdClass;

/**
 * @author Paweł
 */
abstract class Base implements \Interfaces\Model {

    const CACHE_INTERVAL_DAY = 86400;
    const CACHE_INTERVAL_HOUR = 3600;
    const CACHE_INTERVAL_HALF_HOUR = 1800;
    const CACHE_INTERVAL_5_MINUTES = 300;
    const CACHE_INTERVAL_WEEK = 604800;

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

    /** @var  SQLiteWrapper */
    protected $db;

    /** @var  Apc */
    protected $cache;

    /**
     * @param array $aParams
     */
    public function __construct(array $aParams = null)
    {
        $this->aParams = $aParams;
        $this->db = \Database\Factory::getInstance();
        $this->cache = \phpCache\Factory::getInstance()->create();
    }

}