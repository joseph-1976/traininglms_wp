<?php

/*
	Static functions
*/
// TODO: consider modifying static functions to feed into _get_field and _set_field
function ttp_lms_topic_valid( $topic_id ) {
	global $wpdb;
	// These get_var queries are (supposedly) cached by WP
	// consider doing error out here, with optional parameter , $error_out = true 
	return $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->posts WHERE ID = %d and post_type = %s", $topic_id, ttp_lms_post_prefix(TheTrainingMangerLMS\Constants::TopicPostType)) ) == 1;
}
function ttp_lms_topic_type( $topic_id ) {
	if (!ttp_lms_topic_valid( $topic_id )) ttp_lms_error("The supplied topic is invalid");
	return get_post_meta( $topic_id, ttp_lms_prefix('topic_type'), true );
}

?>
