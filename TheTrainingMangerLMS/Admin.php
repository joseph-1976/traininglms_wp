<?php
namespace TheTrainingMangerLMS;

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

import('TheTrainingMangerLMS.Admin.Dashboard');
import('TheTrainingMangerLMS.Admin.DashboardAjaxHandler');
import('TheTrainingMangerLMS.Admin.CourseBuilder');
import('TheTrainingMangerLMS.Admin.CourseBuilderAjaxHandler');
import('TheTrainingMangerLMS.Admin.CourseListAjaxHandler');

final class Admin {
	const HANDLE = 'admin';
	const MENU_HANDLE = 'TheTrainingMangerLMS';
	private $hooks = array();
	private $courseBuilder;
	private $quizBuilder;

	private $settings;
	private $errors;
	private $error_callback;

	public function __construct() {
		// if we're only doing ajax, then there is no need to load up the full Admin
		if (is_ajax()) {
			// we have no way of knowing if the request is for one of our pages at this stage, so add all ajax listeners
			Admin\DashboardAjaxHandler::addHandler();
			Admin\CourseBuilderAjaxHandler::addHandler();
			Admin\CourseListAjaxHandler::addHandler();
		} else {
			$this->setup_actions();
			//$this->setup_filters();
			$this->dashboard = new Admin\Dashboard();
			$this->courseBuilder = new Admin\CourseBuilder();
			//$this->quizBuilder = new Admin\QuizBuilder();
		}
	}

	private function setup_actions() {
		add_action( 'admin_menu',     array( $this, 'create_menu') );
		add_action( 'admin_enqueue_scripts',  array( $this, 'enqueue_scripts'          ), 0, 1 );
		add_action( 'admin_footer-edit-tags.php', array( $this, 'remove_category_tag_slug' ) );
	}

	private function setup_filters() {
		//add_filter('redirect_post_location', array( $this, 'track_errors') );
		add_filter('acf/validate_value/name=url_short_name', array( $this, 'acf_validate_url_short_name'), 10, 4 );
	}

	public function create_menu() {
		add_menu_page(__("Training LMS", "TheTrainingMangerLMS"),
			__("Training LMS", "TheTrainingMangerLMS"), 'manage_options', self::MENU_HANDLE,
			null, 'dashicons-welcome-widgets-menus', '4.111');
		$hook = $this->dashboard->addSubMenu(self::MENU_HANDLE);
		array_push($this->hooks, $hook);
		$hook = $this->courseBuilder->addSubMenu(null);//self::MENU_HANDLE);
		array_push($this->hooks, $hook);
		//$this->quizBuilder->addSubMenu(null);
	}

	public function enqueue_scripts( $hook ) {
		// is this a Training Partners Admin page
		//if (($hook == $this->courseBuilder->getHook()) || ($hook == $this->quizBuilder->getHook())) {
		if (in_array($hook, $this->hooks)) {
			// based on $hook, we decide what to enqueue
			// switch $hook

			//wp_enqueue_script('jquery-ui-core');
			// load jquery ui style (eventually allow for local theme(s) as well)
			// TODO: $theme = thetrainingpartners()->getOptions()->get('jquery-ui_style');
		//	$theme = "le-frog";
			// load the WordPress jQuery UI version
		//	global $wp_scripts;
		//	$ver = "1.11.1";//$wp_scripts->query('jquery-ui-core')->ver;
		//	wp_enqueue_style('jquery-ui-style', "http://ajax.googleapis.com/ajax/libs/jqueryui/" . $ver . "/themes/" . $theme . "/jquery-ui.min.css", false, null);
		//	wp_register_script(ttp_lms_prefix('admin-core'), plugin_dir_url( __FILE__ ) . 'Admin/js/admin-core.js', array('jquery', 'underscore', 'jquery-ui-dialog'), NULL, false);
		//	wp_enqueue_script(ttp_lms_prefix('admin-core'));
		}
	}

	/**
	 * Validation hook for the ACF URL Short Name field located under the Category edit
	 * page.
	 */
	public function acf_validate_url_short_name( $valid, $value, $field, $post_id ) {
		if ( !$valid ) { return $valid; }
		// check sanitization
		$san_value = sanitize_title( $value );
		if (strcmp($value, $san_value) != 0) {
			return "The URL you provided is invalid; consider $san_value instead.";
		}
		// check for slug conflict (categories and pages), but if editing, ignore existing entry
		$term = get_term_by('slug', $value, 'category');
		if (($term && (strcmp($post_id, 'category_' . $term->term_id) != 0)) ||
			(new \WP_Query(array('post_type' => 'page', 'name' => $value)))->found_posts) {
			return "The short name chosen is already in use. Please choose another name.";
		}
		return $valid;
	}

	/**
	 * Provide JavaScript to hide the slug field from the Category create and edit
	 * pages.
	 */
	public function remove_category_tag_slug() {
		// TODO: Put this in the footer and make it run immediately
		global $current_screen;
		if (strcmp($current_screen->id, 'edit-category') == 0) {
?>
    <script type="text/javascript">
    jQuery(document).ready( function($) {
        if ($('#tag-slug').exists() ) {
        	$('#tag-slug').parent().remove();
        }
        if ($('#slug').exists() ) {
        	$('#slug').parent().parent().remove();
        }
    });
    </script>
<?php
		}
	}

}

?>