<?php

/* static functions */
function ttp_lms_user_valid( $user_id ) {
	global $wpdb;
	// These get_var queries are (supposedly) cached by WP
	// consider doing error out here, with optional parameter , $error_out = true 
	return $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->users WHERE id = %d", $user_id) ) == 1;
}
function ttp_lms_user_has_course( $user_id, $course_id ) {
	if (!ttp_lms_user_valid( $user_id )) ttp_lms_error("Invalid user id");
	if (!ttp_lms_course_valid( $course_id )) ttp_lms_error("Invalid course id");
	return get_user_meta($user_id, ttp_lms_prefix('course'), true) == $course_id;
}
function ttp_lms_user_course_status( $user_id, $course_id ) {
	if (!ttp_lms_user_has_course( $user_id, $course_id )) ttp_lms_error("User not taking course");
	return get_user_meta($user_id, ttp_lms_prefix('course_' . $course_id . '_status'), true);
}
function ttp_lms_user_courses( $user_id, $status = 'all' ) {
	if (!ttp_lms_user_valid( $user_id )) ttp_lms_error("Invalid user id");
	global $wpdb;
	if (is_string($status)) {
		if ($status == 'all')
		$query = $wpdb->prepare(
			"SELECT IFNULL(GROUP_CONCAT(c.meta_value SEPARATOR ','), '') AS users FROM $wpdb->usermeta c
			WHERE user_id = %d AND c.meta_key = CONCAT(%s, c.meta_value)",
			$user_id, ttp_lms_prefix('course_')
		);
		else ttp_lms_error("Unknown status value.");
	} elseif (is_array($status)) {
		foreach($status as $value) {
			if (!in_array($value, Course::getStatuses()))
				ttp_lms_error("Unknown status value {$value}.");
		}
		$query = $wpdb->prepare( // s.meta_key = CONCAT(c.meta_key, '_status')
			"SELECT IFNULL(GROUP_CONCAT(c.meta_value SEPARATOR ','), '') AS users FROM $wpdb->usermeta c
			JOIN $wpdb->usermeta s USING(user_id)
			WHERE user_id = %d AND c.meta_key = LEFT(s.meta_key, LENGTH(s.meta_key) - LENGTH('_status'))
			AND FIND_IN_SET(s.meta_value, %s)", $user_id, implode(',', $status)
		);
	} else {
		ttp_lms_error("Unknown status value.");
	}
	$courses = $wpdb->get_var($query);
	return $courses == '' ? array() : explode(',', $courses);
}
?>
