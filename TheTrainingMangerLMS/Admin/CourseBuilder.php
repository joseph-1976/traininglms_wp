<?php
namespace TheTrainingMangerLMS\Admin;

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

import('TheTrainingMangerLMS.Admin.AdminPage');
import('TheTrainingMangerLMS.Content.MyAccount.CourseBuilder');
import('TheTrainingMangerLMS.Constants');

final class CourseBuilder extends AdminPage {
const HANDLE = "admin_course_builder";
const MENU_HANDLE = "course_builder";

	public function __construct() {
		parent::__construct();
		$this->setup_actions();
		$this->setup_filters();
	}

	private function setup_actions() {
		add_action( 'admin_enqueue_scripts',   array( $this, 'enqueue_scripts'          ), 0, 1 );
		add_action( 'current_screen',          array( $this, 'redirect_as_needed') );
	}

	private function setup_filters() {
	}

	public function addSubMenu( $parent ) {
		$this->hook = add_submenu_page($parent, 'Course Builder', 'Course Builder', 
				'manage_options', ttp_lms_prefix(self::MENU_HANDLE), array($this, 'content'));
		add_action('admin_footer-' . $this->hook, array( $this, 'include_templates'));
		return $this->hook;
	}

	public function enqueue_scripts( $hook ) {
		if ($hook == $this->hook) {
			\TheTrainingMangerLMS\Content\MyAccount\CourseBuilder::enqueue_scripts();
		}
	}

	public function include_templates() {
		// *sigh* WP does not pass the $hook
		//$hook = $GLOBALS['hook_suffix'];
		//if ($hook == $this->hook) {
			\TheTrainingMangerLMS\Content\MyAccount\CourseBuilder::include_templates();
		//}
	}

	public function redirect_as_needed( $screen ) {
		// redirect new and edit course requests and stop new and edit lesson and new and edit topic requests
		if (($screen->post_type == ttp_lms_post_prefix(\TheTrainingMangerLMS\Constants::CoursePostType)) &&
			($screen->base == 'post')) {
			// there is a bug in the core, 'action' doesn't get set for 'edit', use $_GET instead
			if (isset($_GET['action']) && ($_GET['action'] == 'edit')) {
				// Note $_POST['post_ID'] is used in the core as well but there is no indication this is ever used
				if (isset($_GET['post']) && ttp_lms_course_valid((int)$_GET['post'])) {
					$url = admin_url('admin.php?page=' . self::MENU_HANDLE . '&action=edit&course_id=' . $_GET['post']);
				} else {
					// invalid post id, redirect to Course listing
					$url = admin_url('edit.php?post_type=' . $screen->post_type);
				}
			} elseif ($screen->action == 'add') {
				$url = admin_url('admin.php?page=' . self::MENU_HANDLE . '&action=add');
			}
		} elseif ((($screen->post_type == ttp_lms_post_prefix(\TheTrainingMangerLMS\Constants::LessonPostType)) ||
			($screen->post_type == ttp_lms_post_prefix(\TheTrainingMangerLMS\Constants::TopicPostType))) &&
			($screen->base == 'post')) {
			// this may change later, but for now
			$url = admin_url('edit.php?post_type=' . ttp_lms_post_prefix(\TheTrainingMangerLMS\Constants::CoursePostType));
		}
		if (isset($url))
			wp_safe_redirect($url);
	}

	public function content() {
		\TheTrainingMangerLMS\Content\MyAccount\CourseBuilder::content();

	}

}

?>