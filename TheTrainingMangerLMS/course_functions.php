<?php

/*
	Static functions
*/
// TODO: consider modifying static functions to feed into _get_field and _set_field
function ttp_lms_course_valid( $course_id ) {
	global $wpdb;
	// These get_var queries are (supposedly) cached by WP
	// consider doing error out here, with optional parameter , $error_out = true 
	return $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->posts WHERE ID = %d and post_type = %s", $course_id, ttp_lms_post_prefix(TheTrainingMangerLMS\Constants::CoursePostType)) ) == 1;
}
function ttp_lms_course_get_field( $course_id, $field_name ) {
	if (!ttp_lms_course_valid( $course_id )) ttp_lms_error("The supplied course is invalid");
	$courseType = get_post_meta( $course_id, ttp_lms_prefix('course_type'), true );
	$fields = $courseType::getFieldsDescriptor();
	if (!array_key_exists($field_name, $fields)) ttp_lms_error($field_name . " is not a field in " . $courseType);
	$source = $fields[$field_name]['source'];
	if ($source == 'postmeta')
		return get_post_meta( $course_id, ttp_lms_prefix('course_' . $field_name), true );
	elseif ($source == 'post') {
		return get_post_field( $fields[$field_name]['wp_name'], $course_id, 'db' );
	}
}
function ttp_lms_course_set_field( $course_id, $field_name, $field_value ) {
	if (!ttp_lms_course_valid( $course_id )) ttp_lms_error("The supplied course is invalid");
	$courseType = get_post_meta( $course_id, ttp_lms_prefix('course_type'), true );
	$fields = $courseType::getFieldsDescriptor();
	if (!array_key_exists($field_name, $fields)) ttp_lms_error($field_name . " is not a field in " . $courseType);
	$source = $fields[$field_name]['source'];
	if ($source == 'postmeta')
		return update_post_meta( $course_id, ttp_lms_prefix('course_' . $field_name), $field_value );
	elseif ($source == 'post') {
		return wp_update_post( array( 'ID' => $course_id, $fields[$field_name]['wp_name'] => $field_value ) );
	}
}
function ttp_lms_course_get_type( $course_id ) {
	if (!ttp_lms_course_valid( $course_id )) ttp_lms_error("The supplied course is invalid");
	return get_post_meta( $course_id, ttp_lms_prefix('course_type'), true );
}
function ttp_lms_course_get_lessons( $course_id ) {
	if (!ttp_lms_course_valid( $course_id )) ttp_lms_error("The supplied course is invalid");
	return explode(',', get_post_meta( $course_id, ttp_lms_prefix('course_lessons'), true));
}
function ttp_lms_course_add_lesson( $course_id, $lesson_id ) {
	if (!ttp_lms_course_valid( $course_id )) ttp_lms_error("The supplied course is invalid");
	if (!ttp_lms_lesson_valid( $lesson_id )) ttp_lms_error("The supplied lesson is invalid");
	$lessons = ttp_lms_course_lessons( $course_id );
	if (in_array($lesson_id, $lessons)) ttp_lms_error("The course already has this lesson");
	array_push($lessons, $lesson_id);
	$lessons = implode(',', $lessons);
	update_post_meta( $course_id, ttp_lms_prefix('course_lessons'), $lessons);
}
function ttp_lms_course_insert_lesson( $course_id, $index, $lesson_id ) {
	if (!ttp_lms_course_valid( $course_id )) ttp_lms_error("The supplied course is invalid");
	if (!ttp_lms_lesson_valid( $lesson_id )) ttp_lms_error("The supplid lesson is invalid");
	// validate $index
	if (!is_int($index)) ttp_lms_error("The supplied index must be an integer.");
	$lessons = ttp_lms_course_get_lessons( $course_id );
	if (($index < 0) || ($index > count($lessons))) ttp_lms_error("The supplied index is not within bounds.");
	// make sure the lesson isn't already added
	if (in_array($lesson_id, $lessons)) ttp_lms_error("The lesson has already been added.");
	// save it
	array_splice($lessons, $index, 0, $lesson_id);
	update_post_meta( $course_id, ttp_lms_prefix('course_lessons'), $lessons);
}
function ttp_lms_course_remove_lesson( $course_id, $index ) {
	if (!ttp_lms_course_valid( $course_id )) ttp_lms_error("The supplied course is invalid");
	// validate index
	if (!is_int($index)) ttp_lms_error("The supplied index must be an integer.");
	$lessons = ttp_lms_course_get_lessons( $course_id );
	if (($index < 0) || ($index > count($lessons))) ttp_lms_error("The supplied index is not within bounds.");
	$lessons = array_merge(array_slice($lessons, 0, $index), 
			array_slice($lessons, $index+1, count($lessons)-1));
	update_post_meta( $course_id, ttp_lms_prefix('course_lessons'), $lessons);
}
function ttp_lms_course_user_can_access( $course_id, $user_id ) {
	if (!ttp_lms_course_valid( $course_id )) ttp_lms_error("The supplied course is invalid");
	if (!ttp_lms_user_valid( $user_id )) ttp_lms_error("The supplied user is invalid");
	if (get_post_meta( $course_id, ttp_lms_prefix('course_access_control_enabled'), true) != 'true') return true; //verses
	//if (ttp_lms_course_field( $course_id, 'access_control_enabled') != 'true') return true;
	$status = get_user_meta( $user_id, ttp_lms_prefix('course_' . $course_id . '_status'), true);
	return $status != Course::EXPIRED;
}
function ttp_lms_course_expire_user( $course_id, $user_id ) {
	if (!ttp_lms_user_has_course( $user_id, $course_id )) ttp_lms_error("User is not taking this course."); // checks course id and user id for validity
	update_user_meta($user_id, ttp_lms_prefix('course_' . $course_id . '_status'), 'expired');
}
function ttp_lms_course_is_user_expired( $course_id, $user_id ) {
	if (!ttp_lms_user_has_course( $user_id, $course_id )) ttp_lms_error("User is not taking this course."); // checks course id and user id for validity
	return get_user_meta($user_id, ttp_lms_prefix('course_' . $course_id . '_status'), true) != Course::EXPIRED;
}
function ttp_lms_course_get_prerequisites( $course_id ) {
	if (!ttp_lms_course_valid( $course_id )) ttp_lms_error("The supplied course is invalid");
	return explode(',', get_post_meta( $course_id, ttp_lms_prefix('course_prerequisites'), true));
}
function ttp_lms_course_add_prerequisite( $course_id, $prereq_id ) {
	if (!ttp_lms_course_valid( $prereq_id )) ttp_lms_error("The supplied prereq is invalid");
	$prereqs = ttp_lms_course_prerequisites( $course_id );
	if (in_array($prereq_id, $prereqs)) ttp_lms_error("The course already has this prerequisite");
	array_push($prereqs, $prereq_id);
	update_post_meta( $course_id, ttp_lms_prefix('course_prerequisites'), implode(',', $prereqs));
}
function ttp_lms_course_remove_prerequisite( $course_id, $prereq_id ) {
	$prereqs = ttp_lms_course_prerequisites( $course_id );
	$index = array_search($prereq_id, $prereqs);
	if ($index === FALSE) ttp_lms_error("The course doesn't have this prerequisite");
	$prereqs = array_merge(array_slice($this->prerequisites, 0, $index), array_slice($this->prerequisites, $index+1, count($this->prerequisites)-1));
	update_post_meta( $course_id, ttp_lms_prefix('course_prerequisites'), implode(',', $prereqs));
}
function ttp_lms_course_has_prerequisite( $course_id, $prereq_id ) {
	if (!ttp_lms_course_valid( $prereq_id )) ttp_lms_error("The supplied prereq is invalid");
	return in_array($prereq_id, ttp_lms_course_prerequisites( $course_id ));
}

?>
