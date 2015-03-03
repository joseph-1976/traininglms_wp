<?php
namespace TheTrainingMangerLMS\Content\MyAccount;

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

import('TheTrainingMangerLMS.Constants');
import('TheTrainingMangerLMS.Admin.CourseListAjaxHandler');
import('TheTrainingMangerLMS.Trainer');

final class CourseList {

const HANDLE = "course_list";

private static $templates = array();

	public static function get_handle() {
		return self::HANDLE;
	}

	public function render() {
		// IDEA: global $wp_contentData
		// make sure the logged in Trainer has a current TC
		//$trainer = \TheTrainingMangerLMS\Trainer::instance(get_current_user_id());
		/*if (is_null($trainer->getCurrentTC())) {
			wp_safe_redirect(site_url('/' . \TheTrainingMangerLMS\Constants::AccountPage . '/'), 302);
			exit;
		}*/
		add_action( 'wp_enqueue_scripts', array(get_class($this), 'enqueue_scripts') );
		add_action( 'get_content', array(get_class($this), 'content') );
		add_action( 'wp_print_footer_scripts', array(get_class($this), 'include_templates') );
	}

	public static function enqueue_scripts() {
			wp_deregister_script( 'jquery-ui-menu' ); // The stock jquery-ui-menu is broken.
			wp_register_script( 'jquery-ui-menu', plugin_dir_url( __FILE__ ) . 'js/jquery.ui.menu.js', array('jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-position'), NULL, false);
			wp_register_script( 'jquery-ui-dropdownmenu', plugin_dir_url( __FILE__ ) . 'js/jquery.ui.dropdownmenu.js', array('jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-position', 'jquery-ui-menu'), NULL, false);
			wp_register_style( 'jquery-ui-dropdownmenu', plugin_dir_url( __FILE__ ) . 'css/jquery.ui.dropdownmenu.css', array(), NULL, 'all');
			wp_register_script( 'jquery-ui-selectmenu', plugin_dir_url( __FILE__ ) . 'js/jquery.ui.selectmenu.js', array('jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-position', 'jquery-ui-menu'), NULL, false);
		//http://cdn.datatables.net/1.10.4/css/jquery.dataTables.css
		wp_enqueue_style('font-awesome', "http://maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css", false, null);
		//also le-frog and //cdn.datatables.net/plug-ins/3cfcc339e89/integration/jqueryui/dataTables.jqueryui.js
		$theme = "le-frog";
		// load the WordPress jQuery UI version
		$ver = "1.11.1";
		wp_enqueue_style('jquery-ui-style', "http://ajax.googleapis.com/ajax/libs/jqueryui/" . $ver . "/themes/" . $theme . "/jquery-ui.min.css", false, null);
//		wp_enqueue_style('jquery-ui-style', plugin_dir_url( __FILE__ ) . 'css/jquery-ui-1.10.3.custom.css', false, null);
//		wp_enqueue_style('jquery-ui-style2', plugin_dir_url( __FILE__ ) . 'css/jquery-ui-1.10.3.theme.css', false, null);
		wp_enqueue_style('jquery-ui-dropdownmenu');
		wp_register_script(ttp_lms_prefix('core'), plugin_dir_url( __FILE__ ) . 'js/core.js', array('jquery', 'underscore', 'jquery-ui-dialog'), NULL, false);
		wp_enqueue_script(ttp_lms_prefix('core'));

		wp_enqueue_script('jquery-datatables', plugin_dir_url( __FILE__ ) . 'js/jquery.dataTables.min.js', array('jquery'), NULL);
		wp_enqueue_script('jquery-maskedinput', plugin_dir_url( __FILE__ ) . 'js/jquery.maskedinput.js', array('jquery'), NULL);
		wp_enqueue_script( self::get_handle(), plugin_dir_url( __FILE__ ) . 'js/course-list.js', array('jquery-datatables', 'jquery-ui-dialog', 'jquery-ui-datepicker', 'jquery-ui-dropdownmenu', 'jquery-ui-selectmenu', ttp_lms_prefix('core')), NULL);
		wp_localize_script( self::get_handle(), 'TheTrainingMangerLMS',
			array(
				'nonce' => wp_create_nonce( \TheTrainingMangerLMS\Admin\CourseListAjaxHandler::getAjaxAction() ),
				'action' => \TheTrainingMangerLMS\Admin\CourseListAjaxHandler::getAjaxAction(),
				'site_url' => site_url(),
				'edit_url' => site_url(\TheTrainingMangerLMS\Constants::AccountPage . '/' . \TheTrainingMangerLMS\Content\MyAccount\CourseEditor::get_handle())
//				'training_company' => \TheTrainingMangerLMS\Trainer::instance(get_current_user_id())->getCurrentTC()->ID()
			)
		);
		wp_enqueue_style( ttp_lms_prefix(self::HANDLE), plugin_dir_url( __FILE__ ) . 'css/course-list.css' );
		wp_enqueue_style( 'jquery-datatables', plugin_dir_url( __FILE__ ) . 'css/jquery.dataTables.css' );
	}

	public static function content() {
		require dirname(__FILE__) . '/html/course-list.php';
	}

	public static function include_templates() {
?>
	<script type="text/javascript">
		var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
	</script>
<?php
	}

}
?>