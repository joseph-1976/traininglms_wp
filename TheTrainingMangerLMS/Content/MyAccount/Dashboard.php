<?php
namespace TheTrainingMangerLMS\Content\MyAccount;

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

import('TheTrainingMangerLMS.Constants');
import('TheTrainingMangerLMS.Admin.CourseBuilder');
import('TheTrainingMangerLMS.Content.MyAccount.CourseListTable');
import('TheTrainingMangerLMS.Admin.DashboardAjaxHandler');

final class Dashboard {

const HANDLE = "dashboard";

private static $templates = array();

	public static function get_handle() {
		return ttp_lms_prefix(self::HANDLE);
	}

	public static function enqueue_scripts( ) {
		//if ($hook == $this->hook) {
			$theme = "le-frog";
			// load the WordPress jQuery UI version
			$ver = "1.11.1";
			wp_enqueue_style('jquery-ui-style', "http://ajax.googleapis.com/ajax/libs/jqueryui/" . $ver . "/themes/" . $theme . "/jquery-ui.min.css", false, null);
			wp_register_script(ttp_lms_prefix('core'), plugin_dir_url( __FILE__ ) . 'js/core.js', array('jquery', 'underscore', 'jquery-ui-dialog'), NULL, false);
			wp_enqueue_script(ttp_lms_prefix('core'));

			wp_enqueue_script('jquery-layout', plugin_dir_url( __FILE__ ) . 'js/jquery.layout.min.js', array('jquery-ui-draggable', 'jquery-effects-slide'), NULL);
			wp_enqueue_script( ttp_lms_prefix(self::HANDLE), plugin_dir_url( __FILE__ ) . 'js/dashboard.js', array('jquery-ui-dialog', ttp_lms_prefix('core')), NULL );
			wp_enqueue_script( ttp_lms_prefix(self::HANDLE) . 'list-table', plugin_dir_url( __FILE__ ) . 'js/list-table.js', false, NULL );
			wp_enqueue_script( 'jquery-ttp-table', plugin_dir_url( __FILE__ ) . 'js/jquery.ttp.table.js', false, NULL );
//			wp_enqueue_script( 'less', "http://raw.githubusercontent.com/less/less.js/v2.0.0-b3/dist/less.min.js");
//				http://cdnjs.cloudflare.com/ajax/libs/less.js/1.7.5/less.min.js");
			if (is_admin()) {
				$edit_url = admin_url('admin.php?page=' . ttp_lms_prefix(\TheTrainingMangerLMS\Admin\CourseBuilder::MENU_HANDLE) . '&action=edit&course_id=');
			} else {
				$edit_url = ""; // FIXME: finish me
			}
			wp_localize_script( ttp_lms_prefix(self::HANDLE), 'TheTrainingMangerLMS',
				array(
					'nonce' => wp_create_nonce( \TheTrainingMangerLMS\Admin\DashboardAjaxHandler::getAjaxAction() ),
					'action' => \TheTrainingMangerLMS\Admin\DashboardAjaxHandler::getAjaxAction(),
					'edit_url' => $edit_url
				)
			);
			wp_enqueue_style( ttp_lms_prefix(self::HANDLE), plugin_dir_url( __FILE__ ) . 'css/dashboard.css' );
			wp_enqueue_style( ttp_lms_prefix(self::HANDLE) . "jlayout", plugin_dir_url( __FILE__ ) . 'css/layout-default.css' );
		//}
	}

	public static function include_templates() {
		foreach (self::$templates as $template) {
			echo "<script type=\"text/template\" id=\"" . $template . "-template\">";
				require dirname(__FILE__) . '/html/' . $template . '.html';
			echo "</script>";
		}
	}

	public static function content() {
		$course_list_table = new \TheTrainingMangerLMS\Content\MyAccount\CourseListTable(array( 'ajax' => true, 'id' => 'course_list_table'));
		require dirname(__FILE__) . '/html/dashboard.php';
	}

}
