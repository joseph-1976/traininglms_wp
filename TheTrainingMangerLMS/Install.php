<?php
namespace TheTrainingMangerLMS;

/**
 * Training LMS Install 'class'
 *
 * This class provides the functions for activating, deactivating,
 * uninstalling, and updating the plugin.  It is non instatiatable
 * and should be used in a static context.
 *
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

//import('TheTrainingMangerLMS.Company');
import('TheTrainingMangerLMS.Constants');

final class Install {

	/* This array provides the post creation values needed to synthesize the Advanced
	 * Custom Fields that will appear on the category admin pages.
	 */
	private static $acf_cat_group =
		array(
			'post_author'  => 1,
			'post_content' => 'a:6:{s:8:\"location\";a:1:{i:0;a:1:{i:0;a:3:{s:5:\"param\";s:8:\"taxonomy\";s:8:\"operator\";s:2:\"==\";s:5:\"value\";s:8:\"category\";}}}s:8:\"position\";s:6:\"normal\";s:5:\"style\";s:7:\"default\";s:15:\"label_placement\";s:3:\"top\";s:21:\"instruction_placement\";s:5:\"label\";s:14:\"hide_on_screen\";a:1:{i:0;s:4:\"slug\";}}',
			'post_title'   => 'TTP Category Fields',
			'post_excerpt' => 'ttp-category-fields',
			'post_status'  => 'publish',
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
			'post_type'    => 'acf-field-group'
		);
	private static $acf_cat_fields = array(
		array(
			'post_author'  => 1,
			'post_content' => 'a:11:{s:4:\"type\";s:4:\"text\";s:12:\"instructions\";s:111:\"Please provide a sanitized text (no spaces or special characters) for the URL stub (http://ttp.com/[url_name]).\";s:8:\"required\";i:1;s:17:\"conditional_logic\";i:0;s:13:\"default_value\";s:0:\"\";s:11:\"placeholder\";s:0:\"\";s:7:\"prepend\";s:0:\"\";s:6:\"append\";s:0:\"\";s:9:\"maxlength\";i:200;s:8:\"readonly\";i:0;s:8:\"disabled\";i:0;}',
			'post_title'   => 'Url Short Name',
			'post_excerpt' => 'url_short_name',
			'post_status'  => 'publish',
			'comment_status' => 'open',
			'ping_status'    => 'open',
			'post_type'    => 'acf-field'
		),
		array(
			'post_author'  => 1,
			'post_content' => 'a:8:{s:4:\"type\";s:7:\"gallery\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:1;s:17:\"conditional_logic\";i:0;s:3:\"min\";s:0:\"\";s:3:\"max\";s:0:\"\";s:12:\"preview_size\";s:9:\"thumbnail\";s:7:\"library\";s:3:\"all\";}',
			'post_title'   => 'Courses Slider Images',
			'post_excerpt' => 'courses_slider_images',
			'post_status'  => 'publish',
			'comment_status' => 'open',
			'ping_status'    => 'open',
			'post_type'    => 'acf-field'
		),
		array(
			'post_author'  => 1,
			'post_content' => 'a:8:{s:4:\"type\";s:7:\"gallery\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:1;s:17:\"conditional_logic\";i:0;s:3:\"min\";s:0:\"\";s:3:\"max\";s:0:\"\";s:12:\"preview_size\";s:9:\"thumbnail\";s:7:\"library\";s:3:\"all\";}',
			'post_title'   => 'Pathway Slider Images',
			'post_excerpt' => 'pathway_slider_images',
			'post_status'  => 'publish',
			'comment_status' => 'open',
			'ping_status'    => 'open',
			'post_type'    => 'acf-field'
		),
		array(
			'post_author'  => 1,
			'post_content' => 'a:8:{s:4:\"type\";s:7:\"gallery\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:1;s:17:\"conditional_logic\";i:0;s:3:\"min\";s:0:\"\";s:3:\"max\";s:0:\"\";s:12:\"preview_size\";s:9:\"thumbnail\";s:7:\"library\";s:3:\"all\";}',
			'post_title'   => 'Certifications Slider Images',
			'post_excerpt' => 'certifications_slider_images',
			'post_status'  => 'publish',
			'comment_status' => 'open',
			'ping_status'    => 'open',
			'post_type'    => 'acf-field'
		),
		array(
			'post_author'  => 1,
			'post_content' => 'a:8:{s:4:\"type\";s:7:\"gallery\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:1;s:17:\"conditional_logic\";i:0;s:3:\"min\";s:0:\"\";s:3:\"max\";s:0:\"\";s:12:\"preview_size\";s:9:\"thumbnail\";s:7:\"library\";s:3:\"all\";}',
			'post_title'   => 'Milestones Slider Images',
			'post_excerpt' => 'milestones_slider_images',
			'post_status'  => 'publish',
			'comment_status' => 'open',
			'ping_status'    => 'open',
			'post_type'    => 'acf-field'
		),
		array(
			'post_author'  => 1,
			'post_content' => 'a:8:{s:4:\"type\";s:7:\"gallery\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:1;s:17:\"conditional_logic\";i:0;s:3:\"min\";s:0:\"\";s:3:\"max\";s:0:\"\";s:12:\"preview_size\";s:9:\"thumbnail\";s:7:\"library\";s:3:\"all\";}',
			'post_title'   => 'Education Credits Slider Images',
			'post_excerpt' => 'educredits_slider_images',
			'post_status'  => 'publish',
			'comment_status' => 'open',
			'ping_status'    => 'open',
			'post_type'    => 'acf-field'
		)
	);

	/**
	 * Prevent Install from being instatiated ever
	 *
	 */
	private function __construct() { trigger_error("Attempting to instantiate a static class", E_USER_ERROR); }

	/**
	 * Prevent Install from being cloned
	 *
	 */
	public function __clone() { trigger_error("Attempting to clone a static class", E_USER_ERROR); }

	public static function validateInstall() {
        global $wpdb;
		// verify we have the page template installed
		$templates = \wp_get_theme()->get_page_templates();
		if (!array_key_exists('page-' . ttp_lms_post_prefix() . '.php', $templates))
			wp_die("TheTrainingMangerLMS Page template is missing.");
		// Verify we have a page with name my-account2 with page template "page-ttp-lms.php"
        $page = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->posts WHERE post_type = 'page' AND post_name = %s", Constants::AccountPage));
		if (is_null($page)) {
			$post_id = wp_insert_post(array( 
				'post_name' => Constants::AccountPage, 
				'post_type' => 'page', 
				'post_status' => 'publish', 
				'post_author' => 1, 
				'page_template' => 'page-' . ttp_lms_post_prefix() . '.php'
			));
			if (is_wp_error($post_id))
				throw new \RuntimeException(serialize($post_id));
			$page = get_post($post_id);
		} else {
			if (($page->post_type != 'page') || ($page->post_status != 'publish'))
				wp_die("TheTrainingMangerLMS Page post entry for my-account is misconfigued; type or status is incorrect.");
			// get page template
			$template = get_post_meta($page->ID, '_wp_page_template', false);
			if (!(count($template) && ($template[0] == 'page-' . ttp_lms_post_prefix() . '.php'))) {
				update_post_meta($page->ID, '_wp_page_template', 'page-' . ttp_lms_post_prefix() . '.php');
			}
		}
		// TODO: make sure the admin user can be instantiated as a TheTrainingMangerLMS\User
	}

	public static function upgrade() {
		$db_version = get_option('ttp_lms_ver', '0.0.0');
		$cur_version = ttp_lms_version();
		if (version_compare($db_version, $cur_version) == 0) {
			return;
		} // for now do a single check...there will probably be several interations after this
		else if (version_compare($db_version, $cur_version) < 0) {
			// synthesize ACF fields, but only if ACF is installed and active
			// might need additional error checking for the inserts
			if (!function_exists('uniqid')) { return; }

			$acf_group = self::$acf_cat_group;
			$acf_group['post_name'] = uniqid('group_');
			$group_id = wp_insert_post($acf_group);

			foreach(self::$acf_cat_fields as $acf_field) {
				$acf_field['post_name'] = uniqid('field_');
				$acf_field['post_parent'] = $group_id;
				wp_insert_post($acf_field);
			}
		}
		update_option('ttp_lms_ver', $cur_version);
	}

	public static function update_rewrite_rules() {
		// TODO: Finish me
		flush_rewrite_rules();
	}

}
