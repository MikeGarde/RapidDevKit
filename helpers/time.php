<?php
class Time extends Helper {
	
	// returns the current time in a given format
	function now($format="Y-m-d H:i:s") {
		return date("Y-m-d H:i:s");
	}
	
	
	// return any date time in a given time format
	function date_time($datetime=null, $format='F d, Y', $echo = true) {
		if(!$datetime) $datetime = time(); else $datetime = strtotime($datetime);
		$date = date($format, $datetime);
		if($echo) echo $date; else return $date;
	}
	
	
	// returns a time as it relates to the present time
	function date_nice($date=null) {
		if(empty($date)) return "No date provided";

		$periods         = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
		$lengths         = array("60","60","24","7","4.35","12","10");
		$now             = time();
		$unix_date         = strtotime($date);
		
		// check validity of date
		if(empty($unix_date)) return "Bad date";

		// is it future date or past date
		if($now > $unix_date) {   
			$difference     = $now - $unix_date;
			$tense         = "ago";
		} else {
			$difference     = $unix_date - $now;
			$tense         = "from now";
		}
		for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) $difference /= $lengths[$j];
		
		$difference = round($difference);
		if($difference != 1) $periods[$j].= "s";
		
		return "$difference $periods[$j] {$tense}";
	}
}
