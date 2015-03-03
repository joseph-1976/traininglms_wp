<?php
namespace TheTrainingMangerLMS\Content;

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

import('TheTrainingMangerLMS.Constants');

final class MyAccount { //implements Routable {

	public function __construct() { // maybe pass in sections such as Default, NotFound and Error and Login???
	}

	public static function route() {
		// very unorthodox, but this may help performance
import('TheTrainingMangerLMS.Content.Login');
//import('TheTrainingMangerLMS.Content.MyAccount.NotFound');
//import('TheTrainingMangerLMS.Content.MyAccount.Dashboard');
import('TheTrainingMangerLMS.Content.MyAccount.CourseBuilder');
import('TheTrainingMangerLMS.Content.MyAccount.CourseList');

		// set-up actions
		// add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		$section = get_query_var('area');
		// Find a subhandler to bind
		if (!is_user_logged_in()) {
			(new Login())->render();
		} else 
		// Is the logged in user a Trainer
		if (!ttp_lms_trainer_valid(get_current_user_id())) { // TODO: is Trainer && is Active Trainer
			(new UnAuthorized())->render();
		} else
		// Is this a root level request (default) or 
		if (is_null($section) || ($section == '')) {
			(new MyAccount\Home())->render();
		} else
		// Is this a specific request for the course list (can't use this as default)
		if ($section == \TheTrainingMangerLMS\Content\MyAccount\CourseList::get_handle()) {
			(new MyAccount\CourseList())->render();
		} else 
		// Is this a specific request for the course builder
		if ($section == \TheTrainingMangerLMS\Content\MyAccount\CourseBuilder::get_handle()) {
			(new MyAccount\CourseBuilder())->render();
		} else {
			(new NotFound())->render();
		}
	}

/*
 * 	public function enqueue_scripts() {
	}

	public function content( $context, $data = null ) {
	}

	public function include_templates() {
	}
*/
}

?>
