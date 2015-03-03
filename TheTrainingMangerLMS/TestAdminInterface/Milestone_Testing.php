<?php
namespace TheTrainingMangerLMS\TestAdminInterface;

import('TheTrainingMangerLMS.Milestone');

/**
 * Training LMS class Milestone Test
 *
 * This class encapsulates methods & attributes
 * needed for testing the Milestone class via the
 * custom post type interface found in the WP admin.
 *
 **/

/*
	The class
*/
class Milestone_Testing {

	private $field_info = array(
		'title' => array( 'title' => 'Title', 'needs_testing_field' => false ),
		'description_short' => array( 'title' => 'Short Description', 'needs_testing_field' => true ),
		'description_medium' => array( 'title' => 'Medium Description', 'needs_testing_field' => true ),
		'description_long' => array( 'title' => 'Long Description', 'needs_testing_field' => false ),
		'tagline' => array( 'title' => 'Tagline', 'needs_testing_field' => true ),
		'main_image' => array( 'title' => 'Main Image', 'needs_testing_field' => false ),
		'associated_tc' => array( 'title' => 'Training Company', 'needs_testing_field' => true ),
		'presale_forum_id' => array( 'title' => 'Presale forum ID', 'needs_testing_field' => true ),
		'cat_id' => array( 'title' => 'Category ID', 'needs_testing_field' => true ),
		'signature_image' => array( 'title' => 'Signature Image', 'needs_testing_field' => true )
	);

	private $current_field;

	/**
	 * Constructor for Milestone Testing class
	 */
	private function __construct() {
		$this->setup_actions();
	}

	/**
	 * sets an instance of the Milestone Testing class
	 */
	public static function Instance() {
		static $instance = null;
		if( $instance === null ) {
			$instance = new Milestone_Testing();
		}
	}

	/**
	 * Adds hooks needed for testing the Milestone class
	 */
	private function setup_actions() {
		add_action( 'save_post',  array( $this, 'save_post' ), 10, 1 );
		add_action( 'add_meta_boxes', array( $this, 'add_milestone_fields' ), 10, 1 );
		add_action( 'admin_head', array( $this, 'fileupload_metabox_header' ), 10, 1 );
	}

	/**
	 * Saves Milestone information on post submission needed for testing
	 */
	public function save_post() {
		$this->milestone = new \TheTrainingMangerLMS\Milestone();
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
			return;
		}

		if( get_post_type() != ttp_lms_post_prefix( \TheTrainingMangerLMS\Constants::MilestonePostType ) ) {
			return;
		}

		remove_action( 'save_post', array( $this, 'save_post' ) );
		foreach( $this->milestone->field_array as $field ) {
			if( 'main_image' == $field || 'title' == $field || 'description_long' == $field ) {
				continue;
			}

			if( 'signature_image' == $field ) {
				if( ! empty( $_FILES ) && isset( $_FILES[$field] ) ) {
					if( ! empty( $_FILES[$field]['tmp_name'] ) ) {
						$this->milestone->set( $field, $_FILES[$field]['tmp_name'] );
					}
				}
				continue;
			}

			if( $this->test_has_value( $_POST[$field] ) ) {
				$this->milestone->set( $field, $_POST[$field] );
			}
	  }
		add_action( 'save_post',  array( $this, 'save_post' ), 10, 1 );
	}

	/**
	 * Adds meta boxes needed for post form in testing Milestone posts
	 */
	public function add_milestone_fields() {
		$this->milestone = new \TheTrainingMangerLMS\Milestone();
		$post_type = ttp_lms_post_prefix( \TheTrainingMangerLMS\Constants::MilestonePostType );

		if( 'publish' == get_post_status() ) {
			add_meta_box( 'display_box',
				'Milestone Information',
				array($this, 'milestone_callback'),
				$post_type,
				'normal',
				'high'
			);
		}


		foreach( $this->milestone->field_array as $field  ) {
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
	 * Writes HTML needed for testing the Milestone class
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
	public function milestone_callback() {
		foreach( $this->milestone->field_array as $field ) {
			echo $this->field_info[$field]['title'].': ';
			if( 'signature_image' == $field ) {
				echo '<br />';
				$image_id = $this->milestone->get( $field );
				$image_attr_array = wp_get_attachment_image_src( $image_id );
				echo '<img src="'.$image_attr_array[0].'" />';
				echo '<br />';
				continue;
			}

			if( 'main_image' == $field ) {
				echo '<br />';
				echo '<img src="'.wp_get_attachment_url($this->milestone->get( 'main_image' )).'" />';
				echo '<br />';
				continue;
			}

			echo $this->milestone->get( $field );
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
		if( get_post_type() != ttp_lms_post_prefix( \TheTrainingMangerLMS\Constants::MilestonePostType ) ) {
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

Milestone_Testing::Instance();
?>