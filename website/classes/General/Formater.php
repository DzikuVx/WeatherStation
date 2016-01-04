<?php

namespace General;

class Formater extends StaticUtils {

    /**
     * @param $date
     * @return string
     */
    static public function formatDate($date)
    {

        if (!is_numeric($date)) {
            $date = strtotime($date);
        }

        $retVal = date("Y-m-d", $date);
        return $retVal;
    }

    /**
     * @param $date
     * @return string
     */
    static public function formatTime($date)
    {

        $retVal = date("H:i", strtotime($date));
        return $retVal;
    }

    /**
     * @param $date
     * @return string
     */
    function getDate($date)
    {

        $retVal = date("Y-m-d", $date);
        return $retVal;
    }

    static public function formatInt($value, $unit = "")
    {

        $retVal = number_format($value, 0, ",", " ") . " " . $unit;
        return $retVal;
    }

    static public function formatFloat($value, $decimal) {
        $value = round($value, $decimal);
    	$retVal = number_format($value, $decimal, ",", " ");
    	return $retVal;
    }
    
    /**
     * Formatuje liczbę
     *
     * @param int $value
     * @return string
     */
    function formatCount($value)
    {

        $retVal = number_format($value, 0, "", " ");
        return $retVal;
    }

}
