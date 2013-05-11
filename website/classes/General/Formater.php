<?php

namespace General;

class Formater extends StaticUtils {
    
    /**
     * Skracanie stringa o podana ilość znaków
     * z dodaniem trzech kropek opcjonalnie 3 parametr link dla "more"
     * @return string  
     */
    
    static public function shortnDot($text, $len, $link=null)
    {
        if(strlen($text) > $len) {        
            $str = substr($text,0,$len);
            $str .="...";
           
            if($link!=null){
                $str .='<a href="'.$link.'"> więcej</a>'; 
            }
            
            
            return $str;
        }
            else return $text;
    }

    /**
     * Obliczenie procentów wartości max.
     *
     * @param numeric $value
     * @param numeric $max
     * @return int
     */
    static public function getPercentage($value, $max)
    {

        if (empty($max)) {
            return 0;
        }

        return round((100 / $max) * $value);
    }

    /**
     * Funkcja formatująca datę do postaci YYYY-MM-DD
     * @param $date
     * @return sformatowana data
     */
    static public function formatDate($date)
    {

        if (!is_numeric($date)) {
            $date = strtotime($date);
        }

        $retVal = date("Y-m-d", $date);
        return $retVal;
    }

    static function getmicrotime()
    {
        list ( $usec, $sec ) = explode(" ", microtime());
        return ((float) $usec + (float) $sec);
    }

    /**
     * Funkcja formatująca datę do postaci YYYY-MM-DD HH:ii
     * @param $date
     * @return sformatowana data
     */
    static public function formatDateTime($date)
    {

        if (!is_numeric($date)) {
            $date = strtotime($date);
        }

        $retVal = date("Y-m-d H:i", $date);
        if ($retVal == '1970-01-01 01:00') {
            $retVal = null;
        }
        return $retVal;
    }

    /**
     * Funkcja formatująca datę do postaci HH-ii
     * @param $date
     * @return sformatowana data
     */
    static public function formatTime($date)
    {

        $retVal = date("H:i", strtotime($date));
        return $retVal;
    }

    /**
     * Funkcja zwaracająca datę w postaci YYYY-MM-DD z UNIX Timestam
     * @param $date
     * @return sformatowana data
     */
    function getDate($date)
    {

        $retVal = date("Y-m-d", $date);
        return $retVal;
    }

    /**
     * Funkcja formatująca wartość do postaci xxx xxx,xx
     * @param $value
     * @param $unit jednostka wartości
     * @return sformatowana wartość
     */
    static public function formatValue($value, $unit = "")
    {

    	if ($value === null) {
    		return '';
    	}
    	
    	if (empty($value)) {
    		$value = 0;
    	}
    	
    	$value = str_replace(",", ".", $value);
    	
        $retVal = number_format($value, 2, ",", " ") . " " . $unit;
        return $retVal;
    }

    static public function formatInt($value, $unit = "")
    {

        $retVal = number_format($value, 0, ",", " ") . " " . $unit;
        return $retVal;
    }

    static public function formatFloat($value, $decimal) {
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

    /**
     * 
     * Pełny trim ze wszystkimi wielokrotynymi spacjami
     * @param string $str
     * @return string
     */
    static public function superTrim($str)
    {

        $char = "\n";
        while (true) {
            if (substr($str, - (strlen($char))) == $char) {
                $str = substr($str, 0, - (strlen($char)));
            }
            else {
                break;
            }

            return trim($str);
        }
    }

    /**
     * Zamiana bool na ciąg Tak/Nie
     * @param boolean $in
     * @return string
     */
    static public function parseYesNo($in)
    {

        if (empty($in)) {
            return \Translate\Controller::getDefault()->get('Nie');
        }
        else {
            return \Translate\Controller::getDefault()->get('Tak');
        }
    }

}
