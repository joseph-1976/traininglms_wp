<?php

/*
	Static functions
*/
// TODO: consider modifying static functions to feed into _get_field and _set_field
function ttp_lms_lesson_valid( $lesson_id ) {
	global $wpdb;
	// These get_var queries are (supposedly) cached by WP
	// consider doing error out here, with optional parameter , $error_out = true 
	return $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->posts WHERE ID = %d and post_type = %s", $lesson_id, ttp_lms_post_prefix(TheTrainingMangerLMS\Constants::LessonPostType)) ) == 1;
}
function ttp_lms_lesson_get_field( $lesson_id, $field_name ) {
	if (!ttp_lms_lesson_valid( $lesson_id )) ttp_lms_error("The supplied lesson is invalid");
	$lesson_type = get_post_meta( $lesson_id, ttp_lms_prefix('lesson_type'), true );
	$fields = $lesson_type::getFieldsDescriptor();
	if (!array_key_exists($field_name, $fields)) ttp_lms_error($field_name . " is not a field in " . $lesson_type);
	$source = $fields[$field_name]['source'];
	if ($source == 'postmeta')
		return get_post_meta( $lesson_id, ttp_lms_prefix('lesson_' . $field_name), true );
	elseif ($source == 'post') {
		return get_post_field( $fields[$field_name]['wp_name'], $lesson_id, 'db' );
	}
}
function ttp_lms_lesson_set_field( $lesson_id, $field_name, $field_value ) {
	if (!ttp_lms_lesson_valid( $lesson_id )) ttp_lms_error("The supplied lesson is invalid");
	$lesson_type = get_post_meta( $lesson_id, ttp_lms_prefix('lesson_type'), true );
	$fields = $lesson_type::getFieldsDescriptor();
	if (!array_key_exists($field_name, $fields)) ttp_lms_error($field_name . " is not a field in " . $lesson_type);
	$source = $fields[$field_name]['source'];
	if ($source == 'postmeta')
		return update_post_meta( $lesson_id, ttp_lms_prefix('lesson_' . $field_name), $field_value );
	elseif ($source == 'post') {
		return wp_update_post( array( 'ID' => $lesson_id, $fields[$field_name]['wp_name'] => $field_value ) );
	}
}
function ttp_lms_lesson_get_type( $lesson_id ) {
	if (!ttp_lms_lesson_valid( $lesson_id )) ttp_lms_error("The supplied lesson is invalid");
	return get_post_meta( $lesson_id, ttp_lms_prefix('lesson_type'), true );
}
function ttp_lms_lesson_get_topics( $lesson_id ) {
	if (!ttp_lms_lesson_valid( $lesson_id )) ttp_lms_error("The supplied lesson is invalid");
	return get_post_meta( $lesson_id, ttp_lms_prefix('lesson_topics'), true);
}
function ttp_lms_lesson_add_topic( $lesson_id, $topic_id ) {
	if (!ttp_lms_lesson_valid( $lesson_id )) ttp_lms_error("The supplied lesson is invalid");
	$topics = ttp_lms_lesson_topics( $lesson_id );
	ttp_lms_lesson_insert_topic( $lesson_id, count($topics), $topic_id);
}
function ttp_lms_lesson_insert_topic( $lesson_id, $index, $topic_id ) {
	if (!ttp_lms_lesson_valid( $lesson_id )) ttp_lms_error("The supplid lesson is invalid");
	if (!ttp_lms_topic_valid( $topic_id )) ttp_lms_error("The supplied topic is invalid");
	// validate $index
	if (!is_int($index)) ttp_lms_error("The supplied index must be an integer.");
	$topics = ttp_lms_lesson_get_topics( $lesson_id );
	if (($index < 0) || ($index > count($topics))) ttp_lms_error("The supplied index is not within bounds.");
	// make sure the topic isn't already added
	if (in_array($topic_id, $topics)) ttp_lms_error("The topic has already been added.");
	// save it
	array_splice($topics, $index, 0, $topic_id);
	update_post_meta( $lesson_id, ttp_lms_prefix('lesson_topics'), $topics);
}
function ttp_lms_lesson_remove_topic( $lesson_id, $topic_id ) {
	if (!ttp_lms_lesson_valid( $lesson_id )) ttp_lms_error("The supplied lesson is invalid");
	if (!ttp_lms_topic_valid( $topic_id )) ttp_lms_error("The supplied topic is invalid");
	$topics = ttp_lms_lesson_get_topics( $lesson_id );
	$index = array_search($topic_id, $topics);
	if ($index === FALSE) ttp_lms_error("The lesson does not have this topic");
	ttp_lms_lesson_remove_topic_by_index( $lesson_id, $index );
}
function ttp_lms_lesson_remove_topic_by_index( $lesson_id, $index ) {
	if (!ttp_lms_lesson_valid( $lesson_id )) ttp_lms_error("The supplied lesson is invalid");
	// validate index
	if (!is_int($index)) ttp_lms_error("The supplied index must be an integer.");
	$topics = ttp_lms_lesson_get_topics( $lesson_id );
	if (($index < 0) || ($index > count($topics))) ttp_lms_error("The supplied index is not within bounds.");
	$topics = array_merge(array_slice($topics, 0, $index), 
			array_slice($topics, $index+1, count($topics)-1));
	update_post_meta( $lesson_id, ttp_lms_prefix('lesson_topics'), $topics);
}

?>
