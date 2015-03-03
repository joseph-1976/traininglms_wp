<?php
namespace TheTrainingMangerLMS\Content\MyAccount;

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

import('TheTrainingMangerLMS.Constants');
import('TheTrainingMangerLMS.Admin.CourseBuilderAjaxHandler');
import('TheTrainingMangerLMS.ContentWrapper');

final class CourseBuilder implements \TheTrainingMangerLMS\Renderable { // extends ContentWrapper implements Activatable/Bindable/WP_Content_Handler {
	use \TheTrainingMangerLMS\ContentWrapper;
const HANDLE = "course_builder";

private static $templates = array('syllabus-lesson', 'syllabus-lesson-topic', 'course-view', 'lesson-view', 'topic-view');

	public function render() { //activate, attach, join, couple
		// get_query_var('course') => translate to course_id || $_POST['course_id']
		//does the logged in user have access to this course via $trainer->getCurrentTC();
		//No -> either redirect back to MyAccount or NotFound (Don't do unauthorized, they'll know there is actually a course)
		//store course_id as valid (no need for the stuff in content now)
		add_action( 'wp_enqueue_scripts',      array(get_class($this), 'enqueue_scripts')   ); // or just use $this, doesn't matter
		// The correct way to do this would be array(new ContentWrapper($this in an interface context), 'content')...
		add_action( 'get_content',             array($this, 'contentWrap')           );
		add_action( 'wp_print_footer_scripts', array(get_class($this), 'include_templates') );
	}

	public static function get_handle() {
		return self::HANDLE;
	}

	public static function enqueue_scripts() {
		//if ($hook == $this->hook) {
			$theme = "le-frog";
			// load the WordPress jQuery UI version
			$ver = "1.11.1";
			wp_enqueue_style('jquery-ui-style', "http://ajax.googleapis.com/ajax/libs/jqueryui/" . $ver . "/themes/" . $theme . "/jquery-ui.min.css", false, null);
			wp_register_script(ttp_lms_prefix('core'), plugin_dir_url( __FILE__ ) . 'js/core.js', array('jquery', 'underscore', 'jquery-ui-dialog'), NULL, false);
			wp_enqueue_script(ttp_lms_prefix('core'));

			wp_deregister_script( 'jquery-ui-menu' ); // The stock jquery-ui-menu is broken.
			wp_register_script( 'jquery-ui-menu', plugin_dir_url( __FILE__ ) . 'js/jquery.ui.menu.js', array('jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-position'), NULL, false);
			wp_register_script( 'jquery-ui-selectmenu', plugin_dir_url( __FILE__ ) . 'js/jquery.ui.selectmenu.js', array('jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-position', 'jquery-ui-menu'), NULL, false);

			wp_enqueue_script('jquery-layout', plugin_dir_url( __FILE__ ) . 'js/jquery.layout.min.js', array('jquery-ui-draggable', 'jquery-effects-slide'), NULL);
			wp_enqueue_script( ttp_lms_prefix(self::HANDLE), plugin_dir_url( __FILE__ ) . 'js/course-builder.js', array('jquery', 'underscore', 'jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-dialog', 'jquery-ui-tabs', 'jquery-ui-accordion', 'jquery-ui-sortable', 'jquery-ui-selectmenu', ttp_lms_prefix('core')), NULL, false );
			wp_enqueue_script('theme-switcher', plugin_dir_url(__FILE__) . 'js/jquery.themeswitcher.min.js', false, NULL, false);
			wp_localize_script( ttp_lms_prefix(self::HANDLE), 'TheTrainingMangerLMS',
				array(
					'nonce' => wp_create_nonce( \TheTrainingMangerLMS\Admin\CourseBuilderAjaxHandler::getAjaxAction() ),
					'action' => \TheTrainingMangerLMS\Admin\CourseBuilderAjaxHandler::getAjaxAction()
				)
			);
			wp_enqueue_style( "jquery-layout", plugin_dir_url( __FILE__ ) . 'css/layout-default.css' );
			wp_enqueue_style( ttp_lms_prefix(self::HANDLE), plugin_dir_url( __FILE__ ) . 'css/course-builder.css' );
		//}
	}

	public static function include_templates() {
		foreach (self::$templates as $template) {
				require dirname(__FILE__) . '/html/' . $template . '.html';
		}
	}

	public function content( $context = '', $data = null ) {
		// check for action and act accordinly, load $course
		if (!array_key_exists('action', $_GET)) {
			$errorMsg = "An error was encountered processing the request: action must be provided.";
		} elseif ($_GET['action'] == 'new') {
			if (!array_key_exists('type', $_GET)) {
				$errorMsg = "";
			} else {
				$parameters = array();
				// look for optional title in parameter
				if (array_key_exists('title', $_GET)) {
					$parameters['title'] = $_GET['title'];
				}
				if ($_GET['type'] == 'online') {
					$course = \TheTrainingMangerLMS\OnlineCourse::create($parameters);
				} elseif ($_GET['type'] == 'live') {
					$course = \TheTrainingMangerLMS\LiveCourse::create($parameters);
				} else {
					$errorMsg = "";
				}
			}
		} elseif ($_GET['action'] == 'edit') {
			if (get_query_var('course')) { $course_id = get_query_var('course');} else $course_id = $_GET['course_id'];
			if (!$course_id) {//array_key_exists('course_id', $_GET)) {
				$errorMsg = "";
			} else {
				try {
					$course = \TheTrainingMangerLMS\Course::instance($course_id);
				} catch (Exception $e) {
					$errorMsg = $e->getMessage();
				}
			}
		} else {
			$errorMsg = "The action parameter is invalid.";
		}
		if (isset($errorMsg))
			// I think it's too late to redirect(?) to an error page
			throw new \TheTrainingMangerLMS\Content\ContentGenerationException($errorMsg);
			//return static::returnErrorContent();

		// load_template is meant for complete replacement of a front page, use get_template_part, which fails silently,
		// or require, which triggers an error if it fails to load.
		require dirname(__FILE__) . '/html/course-builder.php';
	}

}

?>