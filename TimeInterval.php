<?php

class TimeInterval {
private $hours;
private $minutse;
private $seconds;
private $inverted;
public __construct( string $interval_spec ) {
	P%dH%dM%dS-
	I think we should mod seconds by 60, same for minutes  NO, we have normalize for that
	$rollover_minutes = (int)$seconds / 60;
	$seconds = $seconds % 60;
	$minutes += $rollver_minutes;
	$rollver_hours = (int)$minutes / 60;
	$minutes = $minutes % 60;
	$hours += $rollvover_hours;
}

public function setSeconds($seconds) { // make this immutable!  No set methods
	
}
public function getHours() {
	return $this->hours;
}
public function getMinutes() {
	return $this->minutes;
}
public function getSeconds() {
	return $this->seconds;
}

public function isInverted() {
	return $inverted;
}
public function getTotalTime() { // seconds
	return ($this->hours * 60 + $this->minutes) * 60 + $this->seconds;
}
public function normalize() {
	return new TimeInterval();
}

}

?>