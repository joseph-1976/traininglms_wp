<?php

// trainer user functions
function ttp_lms_trainer_valid($trainer_id) {
	// basically we want to know is the User can be instantiated as a Trainer
	global $wpdb;
	return $wpdb->get_var($wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->users JOIN $wpdb->usermeta ON (ID = user_id) WHERE ID = %d AND meta_key = %s", $trainer_id, ttp_lms_prefix('trainer_type'))) == 1;
}

?>
