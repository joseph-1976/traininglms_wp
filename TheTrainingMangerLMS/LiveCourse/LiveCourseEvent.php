<?php
namespace TheTrainingMangerLMS\LiveCourse;

/**
 * Training LMS LiveCourseEvent class
 *
 * This class provides encapsulation and functionality
 * for instances associated with a live course.
 *
 */
import('TheTrainingMangerLMS.LiveCourse.Event');
import('TheTrainingMangerLMS.Utility.Location');
import('TheTrainingMangerLMS.User');

class LiveCourseEvent extends Event {
protected static $fields = array(
	'instructor' => array( 'source' => 'post', 'wp_name' => 'post_author' ),
	'seminars' => array( 'source' => 'postmeta', 'default' => array() ),
	'location'   => array( 'source' => 'postmeta', 'default' => NULL ),
	'maximum_seating' => array( 'source' => 'postmeta', 'default' => 0 ),
	'lunch_provided' => array( 'source' => 'postmeta', 'default' => false ),
	'price' => array( 'source' => 'postmeta', 'default' => '')
);
protected static function getFieldsDescriptor() {
	return array_replace(parent::getFieldsDescriptor(), self::$fields);
}

	private static function existsSeminar($seminars, \DateTime $date) {
		foreach($seminars as $i => $seminar) {
			if ($seminar->date == $date) return $i;
		}
		return -1;
	}

	public function getSeminars() {
		$seminars = array();
		foreach ($this->seminars as $seminar) {
			array_push($seminars, array( 'startTime' => $seminar->startDateTime, 'endTime' => $seminars->endDateTime ));
		}
		return $seminars;
	}
	public function addSeminar(\DateTime $startTime, \DateTime $endTime) {
		//\DateTime $date, \DateTime $startTime, \DateTime $endTime) {
		$seminar = new Seminar( array( 'startDateTime' => $startTime, 'endDateTime' => $endTime ) );
		$seminars = $this->seminars;
		if (static::existsSeminar($seminars, $seminar->date) != -1)
			throw new \InvalidArgumentException("A seminar with this date has already been added.");
		array_push($seminars, $seminar);
		usort($seminars, function($a, $b) { $ad = $a->date; $bd = $b->date; if ($ad > $bd) { return 1; } else if ( $ad == $bd) { return 0; } else { return -1; }});
		$this->start_datetime = $seminars[0]->startDateTime; $this->stop_datetime = $seminars[count($seminars) - 1]->endDateTime;
		$this->seminars = $seminars;
	}
	public function removeSeminar(\DateTime $date) {
		// users may give the startTime, remove time portion from $date
		$date = $date->format('Y-m-d');
		$date = \DateTime::createFromFormat('Y-m-d H:i:s', $date . " 00:00:00");

		$seminars = $this->seminars;
		$index = static::existsSeminar($seminars, $date);
		if ($index == -1)
			throw new \InvalidArgumentException("A seminar with the given date isn't a part of this event.");
		$seminars = array_merge(array_slice($seminars, 0, $index),
			array_slice($seminars, $index+1, count($seminars)-1));
		$this->start_datetime = count($seminars) == 0 ? null : $seminars[0]->startDateTime;
		$this->stop_datetime = count($seminars) == 0 ? null : $seminars[count($seminars) - 1]->endDateTime;
		$this->seminars = $seminars;
	}
	public function updateSeminar(\DateTime $startTime, \DateTime $endTime) {
		$seminar = new Seminar( array( 'startDateTime' => $startTime, 'endDateTime' => $endTime ) );
		$seminars = $this->seminars;
		$index = static::existsSeminar($seminars, $seminar->date);
		if ($index == -1)
			throw new \InvalidArgumentException("A seminar with the given date isn't a part of this event.");
		$seminars[$index] = $seminar;
		$this->start_datetime = $seminars[0]->startDateTime; $this->stop_datetime = $seminars[count($seminars) - 1]->endDateTime;
		$this->seminars = $seminars;
	}

	public function getInstructor() {
		$user_id = $this->instructor;
		return $user_id == 0 ? NULL : \TheTrainingMangerLMS\User::instance($user_id);
	}
	public function setInstructor(\TheTrainingMangerLMS\User $user = NULL) { // Must use = NULL as hack to allow NULL values
		$this->instructor = is_null($user) ? 0 : $user->ID();
	}

	public function getLocation() {
		return $this->location;
	}
	public function setLocation(\TheTrainingMangerLMS\Utility\Location $location) {
		$this->location = $location;
	}

	public function getMaximumSeating() {
		return $this->maximum_seating;
	}
	public function setMaximumSeating($seating) {
		if (!is_integer($seating))
			throw new \InvalidArgumentException("Seating must be an integer.");
		$this->maximum_seating = $seating;
	}

	public function isLunchProvided() {
		return $this->lunch_provided;
	}
	public function setLunchProvided($provided) {
		if (!is_bool($provided))
				throw new \InvalidArgumentException("LunchProvided must be true or false.");
		$this->lunch_provided = $provided;
	}

	public function getAssociatedProduct() {
		return \TheTrainingMangerLMS\Utility::getAssociatedProduct('_event_id', $this->ID());
	}

}


class Seminar {
private static $fields = array( 'startDateTime', 'endDateTime' );
public $date;
public $startDateTime;
public $endDateTime;
//migrate to POO
	public function __construct( $parameters = array() ) {
		foreach( $parameters as $key => $value ) {
			if (!in_array($key, self::$fields))
				throw new \InvalidArgumentException("Unknown parameter {$key}.");
		}
		// all fields are required
		foreach( self::$fields as $key ) {
			if (!array_key_exists($key, $parameters))
				throw new \InvalidArgumentException("{$key} is a required field.");
		}
		// validate fields
		// startTime and endTime must be on the same date
		$startDate = $parameters['startDateTime']->format('Y-m-d');
		$endDate = $parameters['endDateTime']->format('Y-m-d');
		if ($startDate != $endDate)
			throw new \InvalidArgumentException("Start and end dates must be on the same day.");
		$this->date = \DateTime::createFromFormat('Y-m-d H:i:s', $startDate . " 00:00:00");
		foreach( self::$fields as $name ) { // all defaults are ''
			$this->$name = array_key_exists($name, $parameters) ? $parameters[$name] : '';
		}
	}

	public function getDuration() {
		return $this->endDateTime->getTimestamp() - $this->startDateTime->getTimestamp();
	}
}

?>
