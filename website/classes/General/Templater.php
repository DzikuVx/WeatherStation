<?php

namespace General;

use phpCache\CacheKey;
use phpCache\Factory;
use Translate\Controller as TranslateController;

class Templater {

    /**
     * Template file path
     *
     * @var string
     */
    private $fileName;

    /**
     * Template string
     *
     * @var string
     */
    private $template;

    /**
     * @var \Translate\Translate
     */
    private $translation = null;

    /**
     * If template should be cached
     * @var boolean
     */
    static $useCache = false;

    /**
     * @param String $fileName
     * @param \Translate\Translate $translation
     */
    public function __construct($fileName, $translation = null)
    {

        $this->fileName = dirname(__FILE__) . '/../../templates/' . $fileName;
        $this->load();

        $this->translation = $translation;

        if (empty($this->translation)) {
            $this->translation = TranslateController::getDefault();
        }

        return true;
    }

    /**
     * Template load
     *
     */
    private function load()
    {

    	$key = new CacheKey('Templater::load', md5(realpath('') . '|' . $this->fileName));
        $cache = Factory::getInstance()->create();

        if (!self::$useCache || !$cache->check($key)) {

            if (file_exists($this->fileName)) {

                $tFile = fopen($this->fileName, 'r');

                flock($tFile, LOCK_SH);

                $this->template = fread($tFile, filesize($this->fileName));

                flock($tFile, LOCK_UN);
                fclose($tFile);

                $cache->set($key, $this->template, 86400);
            } else {
                throw new \Exception('No file in path: "' . $this->fileName . '"');
            }
        } else {
            $this->template = $cache->get($key);
        }
    }

    /**
     * Template reload
     *
     */
    public function reset()
    {

        $this->load();
    }

    /**
     * Adding new position to template
     *
     * @param mixed $key
     * @param string $value
     * @return boolean
     */
    public function add($key, $value = null)
    {

        try {

            if (!is_array($key) && !is_object($key)) {
                $this->template = str_replace('{' . $key . '}', $value, $this->template);
            }
            else {
                foreach ($key as $tKey => $tValue) {

                    if ($tValue === NULL) {
                        $tValue = '';
                    }

                    $this->add($tKey, $tValue);
                }
            }
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Conditional block removal
     *
     * @param string $key
     */
    public function remove($key)
    {

        $this->template = preg_replace('!({C:' . $key . '}.*{/C:' . $key . '})!ms', '', $this->template);
    }

    /**
     * Template render
     *
     * @return string
     */
    public function get()
    {

        $this->template = preg_replace_callback('!({T:[^}]*})!', array($this, 'translationReplacer'), $this->template);

        $this->template = preg_replace('!({C:[^}]*})!', '', $this->template);
        $this->template = preg_replace('!({/C:[^}]*})!', '', $this->template);

        return $this->template;
    }

    /**
     * Template translation parsing
     *
     * @param array $matches
     * @return string
     */
    private function translationReplacer($matches)
    {
        return $this->translation->get(mb_substr($matches [1], 3, - 1));
    }

    /**
     * __toString magic function
     *
     * @return string
     */
    public function __toString()
    {

        return $this->get();
    }

}