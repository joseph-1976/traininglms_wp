<?php
namespace TheTrainingMangerLMS;

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

//import('TheTrainingParntersLMS.Admin.Settings');
//import('TheTrainingParntersLMS.Admin.ActionMap');
//import('TheTrainingParntersLMS.Admin.Hacks');
//import('TheTrainingParntersLMS.Options');

final class AdminTest {
	const HANDLE = 'admin';
	const MENU_HANDLE = 'TheTrainingMangerLMS';
	private $hook;
	private $courseBuilder;
	private $quizBuilder;

	private $settings;
	private $errors;
	private $error_callback;

	public function __construct() {
		//$this->courseBuilder = new Admin\CourseBuilder();
		//$this->quizBuilder = new Admin\QuizBuilder();
		$this->setup_actions();
		//$this->setup_filters();
	}

	private function setup_actions() {
/*		new \TheTrainingParntersLMS\Admin\ActionMap;
		add_action( af_namespace() . '_admin_init',              array( $this, 'initialize'               ) );*/
		/*add_action( af_namespace() . '_add_meta_boxes',          array( $this, 'add_meta_boxes'           ) );
		add_action( af_namespace() . '_new_content',             array( $this, 'new_content'              ) );
		add_action( af_namespace() . '_updated_article',         array( $this, 'updated_article'          ) );
		add_action( af_namespace() . '_updated_content',         array( $this, 'updated_content'          ) );
		add_action( af_namespace() . '_trashed_article',         array( $this, 'trashed_article'          ) );
		add_action( af_namespace() . '_trashed_content',         array( $this, 'trashed_content'          ) );
		add_action( af_namespace() . '_admin_notices',           array( $this, 'display_errors'           ) );*/

		add_action( 'admin_menu',     array( $this, 'create_menu') );
		//add_action('current_screen', array( $this, 'current_screen'));
		//add_action('load-index.php', array( $this, 'redirect_dashboard') );
		add_action( 'admin_enqueue_scripts',  array( $this, 'enqueue_scripts'          ), 0, 1 );
		add_action( 'wp_ajax_' . ttp_lms_prefix('admin_coursebuilder'), array( $this, 'course_builder_callback' ) );
	}

	private function setup_filters() {
		//add_filter('redirect_post_location', array( $this, 'track_errors') );
	}

	public function create_menu() {
/*		add_menu_page(__("Training LMS", "TheTrainingMangerLMS"),
			__("Training LMS", "TheTrainingMangerLMS"), 'manage_options', self::MENU_HANDLE,
			null, null, null);
		$this->courseBuilder->addSubMenu(self::MENU_HANDLE);
		$this->quizBuilder->addSubMenu(self::MENU_HANDLE);*/
		$this->hook = add_dashboard_page('Training LMS', 'Course Builder', 'read', ttp_lms_prefix('course_builder'), array($this, 'course_builder_content'));
		global $submenu;
		error_log(print_r($submenu, true));
//		add_action('load-'.$this->hook, array( $this, 'enable_course_builder'));
	}

/*	public function current_screen($screen) {
		if ($screen->id == $this->hook) {
			$this->enable_course_builder();
		}
	}
	public function enable_course_builder() {
		error_log("Adding actions");
		if (is_admin()) {
			error_log("Is Admin");
		}
	}*/

	public function enqueue_scripts( $hook ) {
		if ($this->hook == $hook) {
			wp_enqueue_script('admin_coursebuilder', plugin_dir_url( __FILE__ ) . 'Admin/js/test.js' );
			wp_localize_script( 'admin_coursebuilder', 'TheTrainingMangerLMS',
				array(
					'nonce' => wp_create_nonce( ttp_lms_prefix('admin_coursebuilder') )
				)
			);
		}
		// is this a Training Partners Admin page
	/*	if (($hook == $this->courseBuilder->getHook()) || ($hook == $this->quizBuilder->getHook())) {
			wp_enqueue_script('jquery-ui-core');
			// load jquery ui style (eventually allow for local theme(s) as well)
			// TODO: $theme = thetrainingpartners()->getOptions()->get('jquery-ui_style');
			$theme = "start";
			// load the WordPress jQuery UI version
			global $wp_scripts;
			$ver = $wp_scripts->query('jquery-ui-core')->ver;
			wp_enqueue_style('jquery-ui-' . $theme, "http://ajax.googleapis.com/ajax/libs/jqueryui/" . $ver . "/themes/" . $theme . "/jquery-ui.min.css", false, null);
		}*/
	}

	public static function validateAjaxReferer( $action, $die = false ) {
		if ( current_user_can( 'manage_options' ) && check_ajax_referer(ttp_lms_prefix($action), 'nonce', $die )  ) {
			return true;
		}
		// 401: Unauthorized
		self::returnStatusCode(401);
	}

	public static function returnStatusCode( $code, $description = '' ) {
		http_response_code(401);
		echo $description;
    	if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
			wp_die( -1 );
		else
			die( '-1' );
	}

	public static function returnException(\Exception $exception ) {
		$protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
		$code = 420;
		$text = "Internal Error";
        @header($protocol . ' ' . $code . ' ' . $text);
		@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
		wp_send_json(array(
			'errors' => array( 'msg' => $exception->getMessage(), 'code' => $exception->getCode(), 'type' => get_class($exception) )
		));
	}

	public function course_builder_callback() {
		error_log("In Course Builder Callback!");
		$data = self::validateAjaxReferer('admin_coursebuilder');
		//http_response_code(401);
		$protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
		$text = "Moose"; $code = "420";
        @header($protocol . ' ' . $code . ' ' . $text);
		//header("You suck!", true, 401);
		@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
		wp_send_json(array(errors => array('error1', 'error2')));
    	if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
			wp_die();
		else
			die;
		//if ( current_user_can( 'manage_options' ) && check_ajax_referer(ttp_lms_prefix('admin_coursebuilder'), 'nonce')  ) {
			error_log("Preparing to send response!");
			wp_send_json(array(
//				id => 1,
				'success' => true,
				'data' => $data,
//				action => 'options_defaults',
				'nonce' => wp_create_nonce( ttp_lms_prefix('admin_coursebuilder') )
			));
		//}
	}

	public function course_builder_content() {
?>
<div class="wrap about-wrap" style="background-color: white; margin: 0px;">
<h1><?php _e( 'Welcome to My Custom Dashboard Page' ); ?></h1>

 <div class="about-text">
 <?php _e('Donec id elit non mi porta gravida at eget metus. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.' ); ?>
 </div>

 <h2 class="nav-tab-wrapper">
 <a href="#" class="nav-tab nav-tab-active">
 <?php _e( 'Step 1' ); ?>
 </a><a href="#" class="nav-tab">
 <?php _e( 'Step 2' ); ?>
 </a><a href="#" class="nav-tab">
 <?php _e( 'Step 3' ); ?>
 </a>
 </h2>

 <div class="wrap">
		<form action="" method="post" id="form-content">
				<input name="_content[name]" type="text" id="ttp_lms_name" class="regular-text code" value="Sarah Jane" />
				<input name="_content[age]" type="text" id="ttp_lms_age" class="regular-text code" value="23" />
				<table class="submit" width="600px"><tr>
					<td width="25%"><input type="submit" name="submit" class="button-primary" value="<?php esc_attr_e( 'Save', 'TheTrainingMangerLMS' ); ?>" /></td>
					<td width="25%"><input type="button" name="cancel" class="button-primary" onClick="window.location='<?php echo admin_url(); ?>'; return false;" value="<?php esc_attr_e( 'Cancel', 'TheTrainingMangerLMS' ); ?>" /></td>
					<td width="25%">&nbsp;</td>
					<td width="25%"><input type="button" name="defaults" class="button-primary" onClick="updateCourseBuilder(); return false;" value="<?php esc_attr_e( 'Defaults', 'TheTrainingMangerLMS' ); ?>" /></td>
				</tr></table>
		</form>
 <h3><?php _e( 'Morbi leo risus, porta ac consectetur' ); ?></h3>

 <div class="feature-section images-stagger-right">
 <img src="<?php echo esc_url( admin_url( 'images/screenshots/theme-customizer.png' ) ); ?>" class="image-50" />
 <h4><?php _e( 'Risus Consectetur Elit Sollicitudin' ); ?></h4>
 <p><?php _e( 'Cras mattis consectetur purus sit amet fermentum. Vivamus sagittis lacus vel augue laoreet rutrum faucibus dolor auctor. Vestibulum id ligula porta felis euismod semper. Cras justo odio, dapibus ac facilisis in, egestas eget quam. Nulla vitae elit libero, a pharetra augue. Donec sed odio dui.' ); ?></p>

 <h4><?php _e( 'Mattis Justo Purus' ); ?></h4>
 <p><?php _e( 'Aenean lacinia bibendum nulla sed consectetur. Donec id elit non mi porta gravida at eget metus. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum id ligula porta felis euismod semper. Integer posuere erat a ante venenatis dapibus posuere velit aliquet.
Cras mattis consectetur purus sit amet fermentum. Maecenas faucibus mollis interdum. Etiam porta sem malesuada magna mollis euismod. Maecenas faucibus mollis interdum. Curabitur blandit tempus porttitor. Cras justo odio, dapibus ac facilisis in, egestas eget quam.' ); ?></p>
 </div>
 </div>
</div>
<?php
	}
}
?>