<?php
namespace TheTrainingMangerLMS\Admin;

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

import('TheTrainingMangerLMS.Admin.AdminPage');
import('TheTrainingMangerLMS.Content.MyAccount.Dashboard');
//import('TheTrainingMangerLMS.Admin.CourseListTable');
//import('TheTrainingMangerLMS.Constants');

// This now becomes a wrapper around TheTrainingMangerLMS.Content.Dashboard
final class Dashboard extends AdminPage {
const HANDLE = "admin_dashboard";
const MENU_HANDLE = "dashboard";

private static $templates = array();

	public function __construct() {
		parent::__construct();
		$this->setup_actions();
		$this->setup_filters();
		// tables must be declared here for ajax routing or the callback registered and the callback call the appropriate
		// hook or function in the table
	}

	private function setup_actions() {
		add_action( 'admin_enqueue_scripts',   array( $this, 'enqueue_scripts'          ), 0, 1 );
		// add_action( 'current_screen',          array( $this, 'redirect_as_needed') );
		// add_action( 'wp_ajax_course_list_table', array( $this, 'table_callback' ) );
	}

	private function setup_filters() {
	}

	public function addSubMenu( $parent ) {
		$this->hook = add_submenu_page($parent, 'Dashboard', 'Dashboard', 
				'manage_options', ttp_lms_prefix(self::MENU_HANDLE), array($this, 'content'));
		add_action('admin_footer-' . $this->hook, array( $this, 'include_templates'));
		return $this->hook;
	}

	public function enqueue_scripts( $hook ) {
		if ($hook == $this->hook) {
			\TheTrainingMangerLMS\Content\MyAccount\Dashboard::enqueue_scripts();
		}
	}

	public function include_templates() {
		\TheTrainingMangerLMS\Content\MyAccount\Dashboard::include_templates();
	}

	public function content() {
		\TheTrainingMangerLMS\Content\MyAccount\Dashboard::content();
	}

}
