<?php
namespace TheTrainingMangerLMS\Admin;

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

import('TheTrainingMangerLMS.Admin.AjaxActionHandler');
import('TheTrainingMangerLMS.Trainer');

final class CourseListAjaxHandler extends AjaxActionHandler {
const HANDLE = "course-list";

	public static function getAjaxAction() { return ttp_lms_prefix(self::HANDLE); }
	public static function getAjaxMethods() {
		return array(
			'table_data' => array( __CLASS__, 'table_data_callback'),
		);
	}

	public static function addHandler() {
		parent::addHandler();
		//add_action( 'wp_ajax_course_list_table', array( __CLASS__, 'table_callback' ) );
	}

// TODO: move to a trait and add other validate functions
/*	private static function validateTC_ID() {
		if (!isset($_POST['data']['tc_id'])) {
			ttp_lms_log('INVALID', "Missing Training Company ID.", static::getAjaxRequestInfo());
		}
		$tc_id = $_POST['data']['tc_id'];
		if (!ttp_lms_tc_valid($tc_id)) {
			ttp_lms_log('INVALID', "Invalid Training Company ID provided.", static::getAjaxRequestInfo());
			return false;
		}
		$training_company = \TheTrainingMangerLMS\Trainer::instance(get_current_user_id())->getCurrentTC();
		if (is_null($training_company)) {
			ttp_lms_log('INVALID', "Call to course list table with Trainer current TC not set.", static::getAjaxRequestInfo());
			return false;
		}
		if ($training_company->ID() != $tc_id) {
			ttp_lms_log('INVALID', "Supplied Training Company does not match Trainer's current TC.", static::getAjaxRequestInfo());
			return false;
		}
		return true;
	}*/

/**
	Ajax Callback functions
**/
	public function table_data_callback() {
//		if (!self::validateTC_ID())
//			static::returnStatusCode(400);
		// we get the current TC from the logged in user, in case a user may try to spoof the TC
/*		$training_company = \TheTrainingMangerLMS\Trainer::instance(get_current_user_id())->getCurrentTC();
		if (is_null($training_company))
			throw new \InvalidArgumentException("Call to course list table with Trainer current TC not set.");
		if ($training_company->ID() != $_POST['data']['tc_id'])
			throw new \InvalidArgumentException("Supplied Training Company does not match Trainer current TC.");*/

		$data = array(
			array( 'This is name one!', 'LiveCourse', 'Published' ),
			array( 'This is name two!', 'OnlineCourse', 'Draft' ),
			array( 'This is name three!', 'LiveCourse', 'Draft' ),
			array( 'This is name four!', 'OnlineCourse', 'Published' )
			);
		// data => { rows =>, categories =>, trainers? this might depend on the user's role}
		wp_send_json(array('data' => $data));
	}

	public function create_course_callback() {
		if (!isset($_POST['data']['title']) || !isset($_POST['data']['type'])
			|| !(($_POST['data']['type'] == 'online') || ($_POST['data']['type'] == 'live')))
//			|| !self::validateTC_ID())
			static::returnStatusCode(400);

		$title = $_POST['data']['title'];
		if (trim($title) == '') {
			wp_send_json(array('success' => 'false', 'data' => array( 'message' => 'Title must not be blank.')));
		}
		// TODO: check for illegal characters
		// if ()
		// make sure title is unique
		global $wpdb;
		$query = $wpdb->prepare(
			"SELECT count(*) FROM $wpdb->posts p 
			JOIN $wpdb->postmeta m ON (p.ID = m.post_id) 
			WHERE post_type = %s AND m.meta_key = %s",
//			AND m.meta_value = %s AND p.post_title = %s",
			ttp_lms_post_prefix(\TheTrainingMangerLMS\Constants::CoursePostType),
//			ttp_lms_prefix('course_training_company'), $tc_id, 
			$title
		);
		if ($wpdb->get_var($query)) {
			wp_send_json(array('success' => 'false', 'data' => array( 'message' => 'A course with this title already exists.')));
		}
		// we should have title
		$parameters = array('title' => $_POST['data']['title'] );//, 'training_company' => $tc_id);
		if ($_POST['data']['type'] == 'online') {
			$course = \TheTrainingMangerLMS\OnlineCourse::create($parameters);
		} else {
			$course = \TheTrainingMangerLMS\LiveCourse::create($parameters);
		}

		wp_send_json(array('success' => 'true', 'data' => array( 'course' => $course->serialize())));
	}
}
?>
