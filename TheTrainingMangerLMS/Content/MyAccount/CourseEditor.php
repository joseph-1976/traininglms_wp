<?php
namespace TheTrainingMangerLMS\Content\MyAccount;

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

import('TheTrainingMangerLMS.Constants');
//import('TheTrainingMangerLMS.Admin.CourseEditorAjaxHandler');
import('TheTrainingMangerLMS.ContentWrapper');

final class CourseEditor implements \TheTrainingMangerLMS\Renderable { // extends ContentWrapper implements Activatable/Bindable/WP_Content_Handler {
	use \TheTrainingMangerLMS\ContentWrapper;
const HANDLE = "course_editor";

private $course_id = null;

	public function render() {
		// get the course id here and check it; if invalid, redirect to NotFound
		// might be too late to do this
		add_action( 'wp_enqueue_scripts',      array(get_class($this), 'enqueue_scripts')   ); // or just use $this, doesn't matter
		// The correct way to do this would be array(new ContentWrapper($this in an interface context), 'content')...
		add_action( 'get_content',             array($this, 'contentWrap')           );
	//	add_action( 'wp_print_footer_scripts', array(get_class($this), 'include_templates') );
	}

	public static function enqueue_scripts() {
		wp_enqueue_style( ttp_lms_prefix(self::HANDLE), plugin_dir_url( __FILE__ ) . 'css/course-editor.css' );
	}

	public static function get_handle() {
		return self::HANDLE;
	}

	public static function content( $context = '', $data = null ) {
		if (get_query_var('course')) { $course_id = get_query_var('course');} else $course_id = $_GET['course_id'];
		$course = \TheTrainingMangerLMS\LiveCourse::instance($course_id);
		require dirname(__FILE__) . '/html/course-editor.php';
	}

}
?>