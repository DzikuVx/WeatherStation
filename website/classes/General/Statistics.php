<?php

namespace General;

class Statistics extends StaticUtils {
	
	static public function linearRegression($x, $y) {
	
		// calculate number points
		$n = count($x);
		
		// ensure both arrays of points are the same size
		if ($n != count($y)) {
			throw new \Exception('Both arrays must be the same size');	
		}
	
		// calculate sums
		$x_sum = array_sum($x);
		$y_sum = array_sum($y);
	
		$xx_sum = 0;
		$xy_sum = 0;
	
		for($i = 0; $i < $n; $i++) {
	
			$xy_sum+=($x[$i]*$y[$i]);
			$xx_sum+=($x[$i]*$x[$i]);
	
		}
	
		// calculate slope
		$m = (($n * $xy_sum) - ($x_sum * $y_sum)) / (($n * $xx_sum) - ($x_sum * $x_sum));
	
		// calculate intercept
		$b = ($y_sum - ($m * $x_sum)) / $n;
	
		// return result
		return array("m"=>$m, "b"=>$b);
	
	}
	
}