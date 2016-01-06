<?php

namespace General;

use PhpCache\Memcached as Memcached;
use PhpCache\PhpCache;
use Translate\Controller;

class Environment extends StaticUtils
{

    static public function setContentHtml()
    {
        header('Content-Type: text/html; charset=utf-8');
    }

    static public function setContentJson()
    {
        header('Content-Type: application/json; charset=utf-8');
    }

    /**
     * Set environmental variables
     */
    static public function set()
    {

        $config = Config::getInstance();

        ini_set('date.timezone', $config->get('timezone'));
        date_default_timezone_set($config->get('timezone'));

        mb_internal_encoding("UTF-8");
        setlocale(LC_ALL, 'en_US');

        /*
         * Set caching configuration
         */
        PhpCache::$sDefaultMechanism = $config->get('cacheMethod');
        Memcached::$host = $config->get('memcachedIP');
        Memcached::$port = $config->get('memcachedPort');

        Controller::setDefaultLanguage($config->get('language'));

    }
}