<?php
namespace TheTrainingMangerLMS\TestAdminInterface;

import('TheTrainingMangerLMS.InternalCertification');

/**
 * Training LMS class Internal Certification Test
 *
 * This class encapsulates methods & attributes
 * needed for testing the InternalCertification class via the
 * custom post type interface found in the WP admin.
 *
 **/

/*
	The class
*/
class InternalCertification_Testing {

	private $field_info = array(
		'title' => array( 'title' => 'Title', 'needs_testing_field' => false ),
		'description_short' => array( 'title' => 'Short Description', 'needs_testing_field' => true ),
		'description_medium' => array( 'title' => 'Medium Description', 'needs_testing_field' => true ),
		'description_long' => array( 'title' => 'Long Description', 'needs_testing_field' => false ),
		'tagline' => array( 'title' => 'Tagline', 'needs_testing_field' => true ),
		'associated_tc' => array( 'title' => 'Training Company', 'needs_testing_field' => true ),
		'presale_forum_id' => array( 'title' => 'Presale forum ID', 'needs_testing_field' => true ),
		'cat_id' => array( 'title' => 'Category ID', 'needs_testing_field' => true ),
		'signature_image' => array( 'title' => 'Signature Image', 'needs_testing_field' => true ),
		'maintenance_time_interval' => array( 'title' => 'Number of Months In Maintenance Time Interval', 'needs_testing_field' => true ),
		'time_cert_is_active' => array( 'title' => 'Length of Time Cert Is Active (in Months)', 'needs_testing_field' => true ),
		'ec_credits_needed' => array( 'title' => 'Number of Elective Renewal Course Credits', 'needs_testing_field' => true ),
		'associated_tc' => array( 'title' => 'Training Company', 'needs_testing_field' => true ),
		'renewal_process' => array( 'title' => 'Renewal Process Description', 'needs_testing_field' => true ),
		'renewal_fees' => array( 'title' => 'Renewal Fees', 'needs_testing_field' => true ),
		'rep_name' => array( 'title' => 'TC Rep Name', 'needs_testing_field' => true ),
		'rep_role' => array( 'title' => 'TC Rep Role', 'needs_testing_field' => true ),
		'required_renewal_courses' => array( 'title' => 'Required Renewal Courses', 'needs_testing_field' => true )
	);

	private $current_field;

	/**
	 * Constructor for the Internal Certification Testing class
	 */
	private function __construct() {
		$this->setup_actions();
	}

	/**
	 * Sets an instance of the Internal Certification Testing class
	 */
	public static function Instance() {
		static $instance = null;
		if( $instance === null ) {
			$instance = new InternalCertification_Testing();
		}
	}

	/**
	 * Adds required hooks for testing
	 */
	private function setup_actions() {
		add_action( 'save_post',  array( $this, 'save_post' ), 10, 1 );
		add_action( 'add_meta_boxes', array( $this, 'add_certification_fields' ), 10, 1 );
		add_action( 'admin_head', array( $this, 'fileupload_metabox_header' ), 10, 1 );
	}

	/**
	 * Saves required field information on post submission for Internal Certification
	 */
	public function save_post() {
		$this->certification = new \TheTrainingMangerLMS\InternalCertification();
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
			return;
		}

		if( get_post_type() != ttp_lms_post_prefix( \TheTrainingMangerLMS\Constants::InternalCertificationPostType ) ) {
			return;
		}

		remove_action( 'save_post', array( $this, 'save_post' ) );
		foreach( $this->certification->field_array as $field ) {

			if(  'title' == $field || 'description_long' == $field ) {
				continue;
			}

			if( 'signature_image' == $field ) {
				if( ! empty( $_FILES ) && isset( $_FILES[$field] ) ) {
					if( ! empty( $_FILES[$field]['tmp_name'] ) ) {
						$this->certification->set( $field, $_FILES[$field]['tmp_name'] );
					}
				}
				continue;
			}

			if( $this->test_has_value( $_POST[$field] ) ) {
				$this->certification->set( $field, $_POST[$field] );
			}
		}
		add_action( 'save_post',  array( $this, 'save_post' ), 10, 1 );
	}

	/**
	 * Adds fields needed for testing the Internal Certification class
	 */
	public function add_certification_fields() {
		$this->certification = new \TheTrainingMangerLMS\InternalCertification();
		$post_type = ttp_lms_post_prefix( \TheTrainingMangerLMS\Constants::InternalCertificationPostType );
		if( 'publish' == get_post_status() ) {
			add_meta_box( 'display_box',
				'Certification Information',
				array($this, 'certification_callback'),
				$post_type,
				'normal',
				'high'
			);
		}


		foreach( $this->certification->field_array as $field  ) {
			if( ! $this->field_info[$field]['needs_testing_field'] ) {
				continue;
			}

			$this->current_field = $field;
			add_meta_box( $field,
				$this->field_info[$field]['title'],
				array($this, 'field_set_callback'),
				$post_type,
				'normal',
				'high',
				array( 'field_name' => $field )
			);
		}
	}

	/**
	 * Callback function that writes HTML for admin meta boxes
	 * @param $post
	 * @param $metabox
	 */
	public function field_set_callback( $post, $metabox ) {
		$field_name = $metabox['args']['field_name'];
		if( 'signature_image' == $field_name ) {
			$this->image_upload_display( $field_name );
			return;
		}
		echo '<label for="myplugin_new_field">';
		echo $this->field_info[$field_name]['title'];
		echo '</label> ';
		echo '<input type="text" id="'.$field_name.'" name="'.$field_name.'" value="" />';
	}

	/**
	 * Creates HTML for file upload meta box
	 */
	public function image_upload_display( $field_name ) {
		echo '<input id="'.$field_name.'" type="file" name="'.$field_name.'" value="" size="25" />';
	}

	/**
	 * Displays information about the post
	 */
	public function certification_callback() {
		foreach( $this->certification->field_array as $field ) {
			echo $this->field_info[$field]['title'].': ';
			if( 'signature_image' == $field ) {
				echo '<br />';
				$image_id = $this->certification->get( $field );
				$image_attr_array = wp_get_attachment_image_src( $image_id );
				echo '<img src="'.$image_attr_array[0].'" />';
				echo '<br />';
				continue;
			}

			echo $this->certification->get( $field );
			echo '<br />';
		}
	}

	/**
	 * Simple helper method to see if a value exists
	 * @param $value
	 * @return bool
	 */
	public function test_has_value($value) {
		$return = false;
		if( isset( $value ) && ! empty( $value ) ) {
			$return = true;
		}
		return $return;
	}

	/**
	 * Modifies admin edit for to allow for file uploads
	 */
	public function fileupload_metabox_header() {
		if( get_post_type() != ttp_lms_post_prefix( \TheTrainingMangerLMS\Constants::InternalCertificationPostType ) ) {
			return;
		}
		?>
		<script type="text/javascript">
			jQuery(document).ready(function(){
				jQuery('form#post').attr('enctype','multipart/form-data');
				jQuery('form#post').attr('encoding','multipart/form-data');
			});
		</script>
	<?php }

}

InternalCertification_Testing::Instance();
?>