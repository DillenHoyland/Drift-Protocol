<?php
    // mean/std.dev calculators for use with distributions in various places
	function calcMean(array $dist) {
		$mean = 0;
		foreach($dist as $index => $value) {
			// weighted sum of outcome (x-value/index) * probability (y-value/value)
			$mean += $index * $value;
		}
		return $mean;
	}
    function calcStdDev(array $dist, $mean) {
		$variance = 0;
		foreach($dist as $index => $value) {
			// weighted sum of probability (y-value/value) * square of diff from mean (x-value/index - mean)^2
			$variance += $value * ($index - $mean)**2;
		}
		return sqrt($variance);
	}
?>