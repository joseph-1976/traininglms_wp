<?php
namespace TheTrainingMangerLMS;

/**
 * Training LMS Live Course class
 *
 * This class encapsulates fields and functionality
 * specific to Training LMS Live Course.
 *
 **/
import('TheTrainingMangerLMS.Course');
import('TheTrainingMangerLMS.LiveCourse.LiveCourseEvent');

/*
	The class
*/
class LiveCourse extends Course {
protected static $fields = array(
	'events' => array( 'source' => 'postmeta', 'default' => array() ),
	'syllabus' => array( 'source' => 'postmeta', 'default' => '' ),
	'default_event_price' => array( 'source' => 'postmeta', 'default' => ''),
	'official_trainers' => array( 'source' => 'postmeta', 'default' => array() )
);
protected static function getFieldsDescriptor() {
	return array_replace(parent::getFieldsDescriptor(), self::$fields);
}
/**
		Accessor methods
**/
	public function getDefaultEventPrice() {
		return $this->default_event_price;
	}
	public function setDefaultEventPrice($price) {
		$this->default_event_price = $price;
	}

	public function getSyllabus() {
		return $this->syllabus;
	}
	public function setSyllabus($syllabus) {
		$this->syllabus = $syllabus;
	}

/**
	Course Instance functions.
**/
	public function getEvents(\DateTime $from = NULL, \DateTime $to = NULL) {
		if (!is_null($from)) {
			$from_utc = clone $from; $from_utc->setTimezone(new \DateTimeZone('UTC'));
		}
		if (!is_null($to)) {
			$to_utc = clone $to; $to_utc->setTimezone(new \DateTimeZone('UTC'));
		}
		global $wpdb;
		// all
		if (is_null($from) && is_null($to)) {
			$events_filtered = $this->events;
		} elseif (is_null($to)) { // from
			$query = $wpdb->prepare("SELECT GROUP_CONCAT(post_id SEPARATOR ',') as events FROM $wpdb->postmeta WHERE FIND_IN_SET(post_id, %s) AND
				meta_key = 'ttp_lms_event_start_datetime_utc' AND meta_value >= %s ORDER BY meta_value", implode(',', $this->events), $from_utc->format("Y-m-d H:i:s"));
		} elseif (is_null($from)) { // to
			$query = $wpdb->prepare("SELECT GROUP_CONCAT(post_id SEPARATOR ',') as events FROM $wpdb->postmeta WHERE FIND_IN_SET(post_id, %s) AND
				meta_key = 'ttp_lms_event_start_datetime_utc' AND meta_value <= %s ORDER BY meta_value", implode(',', $this->events), $to_utc->format("Y-m-d H:i:s"));
		} else { // from, to
			if ($from_utc > $to_utc)
				throw new \InvalidArgumentException("From date must be before to date.");
			$query = $wpdb->prepare("SELECT GROUP_CONCAT(post_id SEPARATOR ',') as events FROM $wpdb->postmeta WHERE FIND_IN_SET(post_id, %s) AND
				meta_key = 'ttp_lms_event_start_datetime_utc' AND meta_value between %s and %s ORDER BY meta_value", 
				implode(',', $this->events), $from_utc->format("Y-m-d H:i:s"), $to_utc->format("Y-m-d H:i:s"));
		}
		if (count($this->events) == 0) return array(); // while a slight performance hit, the appropriate checks are not bypassed
		if (isset($query)) {
			$events_filtered = $wpdb->get_var($query);
			$events_filtered = $events_filtered == '' ? array() : explode(',', $events_filtered);
			if ($events_filtered == '') $events_filtered = array();
		}
		$events = array();
		foreach($events_filtered as $event_id) {
			array_push($events, \TheTrainingMangerLMS\LiveCourse\Event::instance($event_id));
		}
		return $events;
	}
	public function getAllEvents() {
		return $this->getEvents();
	}
	public function getUpcomingEvents() {
		return $this->getEvents(new \DateTime());
	}
	/**
	 * Returns number LiveCourse's upcoming Events
	 */
	public function getSetNumberOfUpcomingEventsForLiveCourse(){
		return count($this->getEvents(new \DateTime()));

	}
	/**
	 * Returns an array of the LiveCourse's upcoming Events and each Event's associated product
	 */
	public function getUpcomingEventsAndProducts() {
		$events = $this->getUpcomingEvents();
		$rv = array();
		foreach ($events as $event) {

			$rv[] = array('event' => $event, 'product' => $event->getAssociatedProduct());


		}
		return $rv;
	}
	
	public function addEvent(\TheTrainingMangerLMS\LiveCourse\LiveCourseEvent $event) {
		if (in_array($event->ID(), $this->events))
			throw new \InvalidArgumentException("Event is already scheduled.");
		$temp = $this->events;
		array_push($temp, $event->ID());
		$this->events = $temp;
	}
	public function removeEvent(\TheTrainingMangerLMS\LiveCourse\LiveCourseEvent $event) {
		$index = array_search($event->ID(), $this->events);
		if ($index === FALSE)
			throw new \InvalidArgumentException("Event is not scheduled.");
		// slice it up
		$this->events = array_merge(array_slice($this->events, 0, $index), 
			array_slice($this->events, $index+1, count($this->events)-1));
	}

/**
	Class scheduling functions.
**/

	public function hasScheduledInstance(Student $user) {
		if (!$user->hasCourse($this))
			throw new \InvalidArgumentException("Student is not taking this course.");
		global $wpdb;
		$count = $wpdb->get_var($wpdb->prepare("SELECT count(meta_value) as count FROM $wpdb->usermeta WHERE user_id = %d AND meta_key = CONCAT(%s, meta_value)",
			$user->ID(), ttp_lms_prefix('course_'. $this->id . '_event_')));
		return $count == 1;
	}
	public function getScheduledInstance(Student $user) {
		if (!$user->hasCourse($this))
			throw new \InvalidArgumentException("Student is not taking this course.");
		global $wpdb;
		$event_id = $wpdb->get_var($wpdb->prepare("SELECT meta_value as count FROM $wpdb->usermeta WHERE user_id = %d AND meta_key = CONCAT(%s, meta_value)",
			$user->ID(), ttp_lms_prefix('course_'. $this->id . '_event_')));
		return is_null($event_id) ? NULL : \TheTrainingMangerLMS\LiveCourse\Event::instance($event_id);
	}
	// assignClassPending? approveAssignment?
	public function assignInstance(\TheTrainingMangerLMS\LiveCourse\LiveCourseEvent $event, Student $user, $override = false) {
		if (!$user->hasCourse($this))
			throw new \InvalidArgumentException("Student is not taking this course.");
		// has user already signed up for another event (this should probably be left to external logic)
		if ($this->hasScheduledInstance($user))
			throw new \InvalidArgumentException("Student has already scheduled a class for this course.");
		//if ($this->getScheduledClass($user)->ID() == $event->ID())
		//	throw new \InvalidArgumentException("User has already scheduled this event.");
		if (!$override && ($this->getSeatsRemaining($event) <= 0)) { // whether or not to check for availability
			throw new \LogicException("There are no available seats for the event"); // in other software getSeatingsRemaining($event, $lock_object);
		}
		add_user_meta( $user->ID(), ttp_lms_prefix('course_' . $this->id . '_event_' . $event->ID()), $event->ID());
		// unlock($lock_object);
	}
	public function unassignInstance(\TheTrainingMangerLMS\LiveCourse\LiveCourseEvent $event, Student $user) { // TODO: we really don't need $event here
		if (!$user->hasCourse($this))
			throw new \InvalidArgumentException("Student is not taking this course.");
		if (!($this->hasScheduledInstance($user) && ($this->getScheduledInstance($user)->ID() == $event->ID())))
			throw new \InvalidArgumentException("Student has not scheduled this event.");
		delete_user_meta( $user->ID(),  ttp_lms_prefix('course_' . $this->id . '_event_' . $event->ID()));
	}
	public function getSeatsRemaining(\TheTrainingMangerLMS\LiveCourse\LiveCourseEvent $event) {
		if (!in_array($event->ID(), $this->events))
			throw new \InvalidArgumentException("The event does belong to this course.");
		global $wpdb;
		$count = $wpdb->get_var($wpdb->prepare("SELECT count(user_id) AS count FROM $wpdb->usermeta WHERE meta_key = %s and meta_value = %s",
			ttp_lms_prefix('course_' . $this->id . '_event_' . $event->ID()), $event->ID()));
		return $event->getMaximumSeating() - $count;
	}
	public function getUsersListInInstance(\TheTrainingMangerLMS\LiveCourse\LiveCourseEvent $event) {
		global $wpdb;
		$user_list = $wpdb->get_var($wpdb->prepare(
			"SELECT IFNULL(group_concat(user_id SEPARATOR ','), '') AS users FROM $wpdb->usermeta 
			WHERE meta_key = %s and meta_value = %s",
			ttp_lms_prefix('course_' . $this->id . '_event_' . $event->ID()), $event->ID()));
		return $user_list == '' ? array() : explode(',', $user_list);
	}

	// override insertLesson function so only one lesson can be added
	public function insertLesson($index, Lesson $lesson) {
		if (count($this->lessons) == 1)
			throw new \InvalidArgumentException("Live courses can only have one lesson.");
		parent::insertLesson($index, $lesson);
	}

/**
	Official Trainer functions.
**/
	public function getOfficialTrainers() {
		$officialTrainers = array();
		foreach($this->official_trainers as $user_id) {
			// most likely will just be \TheTrainingMangerLMS\User for now
			//$userType = static::getPostObjectType( $user_id );
			array_push($officialTrainers, User::instance($user_id));
		}
		return $officialTrainers;
	}
	public function addOfficialTrainer(Trainer $trainer) {
		if (in_array($trainer->ID(), $this->official_trainers))
			throw new \InvalidArgumentException("The Trainer is already an official of this Course.");
		$temp = $this->official_trainers;
		array_push($temp, $trainer->ID());
		$this->official_trainers = $temp;
	}
	public function removeOfficialTrainer(Trainer $trainer) {
		$index = array_search($trainer->ID(), $this->official_trainers);
		if ($index === FALSE)
			throw new \InvalidArgumentException("The trainer is not an official of this Course.");
		$official_trainers = $this->official_trainers;
		// slice it up
		$this->official_trainers = array_merge(array_slice($official_trainers, 0, $index), 
			array_slice($official_trainers, $index+1, count($official_trainers)-1));
	}
	public function isOfficialTrainer(Trainer $trainer) {
		return in_array($trainer->ID(), $this->official_trainers);
	}

}
?>
