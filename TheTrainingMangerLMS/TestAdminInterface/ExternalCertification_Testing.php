<?php
namespace TheTrainingMangerLMS\TestAdminInterface;

import('TheTrainingMangerLMS.ExternalCertification');

/**
 * Training LMS class External Certification Test
 *
 * This class encapsulates methods & attributes
 * needed for testing the ExternalCertification class via the
 * custom post type interface found in the WP admin.
 *
 **/

/*
	The class
*/
class ExternalCertification_Testing {

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
		'ec_amount' => array( 'title' => 'Total Number of Educational Credits', 'needs_testing_field' => true ),
		'time_cert_is_active' => array( 'title' => 'Length of Time Cert Is Active (in Months)', 'needs_testing_field' => true ),
		'ec_id' => array( 'title' => 'EC-Entity', 'needs_testing_field' => true ),
		'designation' => array( 'title' => 'Designation', 'needs_testing_field' => true ),

	);

	private $current_field;

	/**
	 * Constructor for External Certification Testing class
	 */
	private function __construct() {
		$this->setup_actions();
	}

	/**
	 * Creates an instance of the testing class
	 */
	public static function Instance() {
		static $instance = null;
		if( $instance === null ) {
			$instance = new ExternalCertification_Testing();
		}
	}

	/**
	 * Registers action hooks needed for testing
	 */
	private function setup_actions() {
		add_action( 'save_post',  array( $this, 'save_post' ), 10, 1 );
		add_action( 'add_meta_boxes', array( $this, 'add_certification_fields' ), 10, 1 );
		add_action( 'admin_head', array( $this, 'fileupload_metabox_header' ), 10, 1 );
	}

	/**
	 * Function that saves specific attributes needed by the External Certification
	 * class.
	 */
	public function save_post() {
		$this->certification = new \TheTrainingMangerLMS\ExternalCertification();
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
			return;
		}

		if( get_post_type() != ttp_lms_post_prefix( \TheTrainingMangerLMS\Constants::ExternalCertificationPostType ) ) {
				return;
		}

		remove_action( 'save_post', array( $this, 'save_post' ) );
		foreach( $this->certification->field_array as $field ) {

			if(  'title' == $field || 'description_long' == $field ) {
				continue;
			}

			if( 'signature_image' == $field ) {
				if( ! empty( $_FILES ) && isset( $_FILES[$field] ) ) {
					$this->certification->set( $field, $_FILES[$field] );
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
	 * Adds meta box code to display testing fields
	 */
	public function add_certification_fields() {
		$this->certification = new \TheTrainingMangerLMS\ExternalCertification();
		$post_type = ttp_lms_post_prefix( \TheTrainingMangerLMS\Constants::ExternalCertificationPostType );
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
	 * Call back function that writes HTML for meta boxes
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
	 * Display a file upload box for testing
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
				$image_attr_array = $this->certification->get( $field );
				echo '<img src="'.$image_attr_array['url'].'" />';
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
	 * Modifies form post header in admin to allow file uploading
	 */
	public function fileupload_metabox_header() {
		if( get_post_type() != ttp_lms_post_prefix( \TheTrainingMangerLMS\Constants::ExternalCertificationPostType ) ) {
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

ExternalCertification_Testing::Instance();
?>