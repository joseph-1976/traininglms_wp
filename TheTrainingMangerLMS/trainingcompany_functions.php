<?php

function ttp_lms_tc_valid( $tc_id ) {
	global $wpdb;
	// These get_var queries are (supposedly) cached by WP
	// consider doing error out here, with optional parameter , $error_out = true 
	return $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->posts WHERE ID = %d and post_type = %s", $tc_id, ttp_lms_post_prefix(TheTrainingMangerLMS\Constants::TrainingCompanyPostType)) ) == 1;
}

?>
