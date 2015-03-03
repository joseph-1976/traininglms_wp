<?php

/**
 * Training LMS Common Functions
 *
 * Common functions to all Training LMS plugin members
 * Provides the prefix(namespace) for all WordPress actions, hooks, and tags
 *
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

import('TheTrainingMangerLMS.Constants');

/** thetrainingpartners_namespace **/
function ttp_lms_prefix( $value = "" ) {
	return $value == "" ? \TheTrainingMangerLMS\Constants::PackagePrefix : \TheTrainingMangerLMS\Constants::PackagePrefix . '_' . $value;
}

function ttp_lms_post_prefix( $value = "" ) {
	return $value == "" ? \TheTrainingMangerLMS\Constants::PostPrefix : \TheTrainingMangerLMS\Constants::PostPrefix . '-' . $value;
}

function ttp_lms_log( $tag = "", $msg = "") {
	// add any needed preprocessing here
	log( $tag, $msg );
}

function ttp_lms_error( $msg = "" ) {
	trigger_error($msg);
}

function ttp_lms_version() {
	return \TheTrainingMangerLMS\Constants::Version;
}

function TheTrainingMangerLMS() {
	return TheTrainingMangerLMS::instance();
}

?>