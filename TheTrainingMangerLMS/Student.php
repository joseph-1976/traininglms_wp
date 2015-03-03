<?php
namespace TheTrainingMangerLMS;

import('TheTrainingMangerLMS.Constants');
import('TheTrainingMangerLMS.User');

class Student Extends User {

	protected static function prefix($key) { return ttp_lms_prefix('student_' . $key); }

	protected static function getFieldsDescriptor() {
		return array_replace(parent::getFieldsDescriptor(), self::$fields);
	}

	protected static $fields = array(
		'has_participated_in_forum' => array( 'default' => 'false', 'source' => 'usermeta' ),
		'has_purchased_course'      => array( 'default' => 'false', 'source' => 'usermeta' ),
		'is_part_of_company'        => array( 'default' => 'false', 'source' => 'usermeta' ),
		'has_created_company'       => array( 'default' => 'false', 'source' => 'usermeta' ),
		'has_added_to_wl'           => array( 'default' => 'false', 'source' => 'usermeta' )
	);

	/**
		Course related functions
	**/
	// FIXME: Move to Student
	function getCoursesList( $status = 'all' ) {
		global $wpdb;
		if (is_string($status)) {
			if ($status == 'all')
				// c.meta_key REGEXP "ttp_lms_course_\\d+" is also acceptable
			$query = $wpdb->prepare(
				"SELECT IFNULL(GROUP_CONCAT(c.meta_value SEPARATOR ','), '') AS users FROM $wpdb->usermeta c
				WHERE user_id = %d AND c.meta_key = CONCAT(%s, c.meta_value)",
				$this->id, ttp_lms_prefix('course_')
			);
			else throw new InvalidArgumentException("Unknown status value.");
		} elseif (is_array($status)) {
			foreach($status as $value) {
				if (!in_array($value, Course::getAccessStatuses()))
					throw new InvalidArgumentException("Unknown status value {$value}.");
			}
			$query = $wpdb->prepare(
				"SELECT IFNULL(GROUP_CONCAT(c.meta_value SEPARATOR ','), '') AS users FROM $wpdb->usermeta c
				JOIN $wpdb->usermeta s USING(user_id)
				WHERE user_id = %d AND s.meta_key = CONCAT(c.meta_key, '_status')
				AND FIND_IN_SET(s.meta_value, %s)", $this->id, implode(',', $status)
			);
		} else {
			throw new InvalidArgumentException("Unknown status value.");
		}
		$courses = $wpdb->get_var($query);
		return $courses == '' ? array() : explode(',', $courses);
	}
	function hasCourse( Course $course ) {
		// Consider moving to _status for course enquiries
		return get_user_meta($this->id, ttp_lms_prefix('course_' . $course->ID()), true) == $course->ID();
	}
	function getCourseStatus( Course $course ) {
		if (!$this->hasCourse($course))
			throw new \InvalidArgumentException("User is not taking this course.");
		return get_user_meta($this->id, ttp_lms_prefix('course_' . $course->ID() . '_status'), true);
	}

	/**
		Quiz related functions
	**/
	public function hasTakenQuiz(Quiz $quiz) {
		$quiz_id = get_user_meta($this->id, ttp_lms_prefix('quiz_' . $quiz->ID()), false);
		$active = get_user_meta($this->id, ttp_lms_prefix('quiz_active'), false);
		return (count($quiz_id) == 1) && ($quiz->ID() == $quiz_id[0]) && !((count($active) == 1) && ($active[0] == $quiz->ID()));
	}
	public function isTakingQuiz() {
		$active = get_user_meta($this->id, ttp_lms_prefix('quiz_active'), false);
		return count($active) == 1;
	}
	// need to mark which quiz the user is taking, so User::isTakingQuiz(); true or return Quiz or NULL

	// make course as archive function
}