<?php
namespace TheTrainingMangerLMS\Admin;

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

import('TheTrainingMangerLMS.Admin.AjaxActionHandler');
import('TheTrainingMangerLMS.Content.MyAccount.CourseListTable');

final class DashboardAjaxHandler extends AjaxActionHandler {
const HANDLE = "dashboard";

	public static function getAjaxAction() { return ttp_lms_prefix(self::HANDLE); }
	public static function getAjaxMethods() {
		return array(
			'new_course' => array( __CLASS__, 'new_course_callback'),
		);
	}

	public static function addHandler() {
		parent::addHandler();
		add_action( 'wp_ajax_course_list_table', array( __CLASS__, 'table_callback' ) );
	}

/**
	Ajax Callback functions
**/
	public function table_callback() {
		$course_list_table = new \TheTrainingMangerLMS\Content\CourseListTable(array( 'ajax' => true, 'id' => 'course_list_table'));
		$course_list_table->ajax_user_can();
		$course_list_table->ajax_response();
	}

	public function new_course_callback() {
		// TODO: validate course_type
		// we should have title
		$parameters = array();
		if (isset($_POST['data']['course_title']))
				$parameters['title'] = $_POST['data']['course_title'];
		$course = \TheTrainingMangerLMS\OnlineCourse::create($parameters);
		wp_send_json(array('success' => 'true', 'data' => array('course_id' => $course->ID(), 'course_title' => $course->getTitle())));
	}

}
