<?php
/**
 * Created by PhpStorm.
 * User: pspychalski
 * Date: 03.06.15
 * Time: 09:25
 */

namespace Assets;


trait Privateer {

    /**
     * Method executes protected and private methods of an object using reflection mechanism
     *
     * @param $obj
     * @param $name
     * @param array $args
     * @return mixed
     */
    protected static function callMethod($obj, $name, array $args) {
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method->invokeArgs($obj, $args);
    }

    /**
     * Method return protected or private property of an object
     *
     * @param $obj
     * @param $name
     * @return mixed
     */
    protected static function getProperty($obj, $name) {
        $reflection = new \ReflectionClass($obj);
        $property = $reflection->getProperty($name);
        $property->setAccessible(true);
        return $property->getValue($obj);
    }

    protected static function setProperty($obj, $name, $value) {
        $reflection = new \ReflectionClass($obj);
        $property = $reflection->getProperty($name);
        $property->setAccessible(true);
        $property->setValue($obj, $value);
    }

}