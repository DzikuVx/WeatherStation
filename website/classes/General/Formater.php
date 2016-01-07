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
    
}
