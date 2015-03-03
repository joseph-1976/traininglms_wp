<?php
namespace TheTrainingMangerLMS;

/**
 * Training LMS base Goal class
 *
 * This class encapsulates fields and functionality
 * specific to Training LMS Goal classes.
 * This includes Milestones, Internal Certifications, &
 * External Certifications.
 *
 **/
import('TheTrainingMangerLMS.CommonCPTBase');

abstract class Goal extends CommonCPTBase {

	public function get_base( $field_name ) {
		if( 'signature_image' == $field_name ) {
			$return = $this->get_signature_image();
		} else {
			$return = parent::get_base( $field_name );
		}

		return $return;
	}

	public function set_base( $field_name, $value ) {
		if( 'signature_image' == $field_name ) {
			$return = $this->set_signature_image( $value );
		} else {
			$return = parent::set_base( $field_name, $value );
		}

		return $return;
	}

	/**
	 * Returns the signature image url
	 * @return bool
	 */
	protected function get_signature_image() {
		return $this->get_meta_value( 'signature_image' );
	}

	/**
	 * Sets the signature file given a file path. The file is copied into the
	 * WP uploads directory, an attachment is created for it in WordPress so it can
	 * be accessed via post ID, & metadata is created. Please note that if false is
	 * returned then we must communicate to the user that the file must be a PNG.
	 * @param $file_path
	 * @return bool
	 */
	protected  function set_signature_image( $file_path ) {
		if( ! $this->is_png( $file_path ) ) {
			return false;
		}
		$wp_upload_dir = wp_upload_dir();
		$formatted_name = $this->post_id.'_signature_image.png';
		$path_name = $wp_upload_dir['path'].'/'.$formatted_name;
		move_uploaded_file( $file_path, $path_name );

		$attachment = array(
			'guid'           => $wp_upload_dir['url'] . '/' . $formatted_name,
			'post_mime_type' => 'image/png',
			'post_title'     => $formatted_name,
			'post_content'   => '',
			'post_status'    => 'inherit'
		);

		$attach_id = wp_insert_attachment( $attachment, $path_name, $this->post_id );
		if( ! empty( $attach_id ) ) {
			$return = $this->set_meta_value( 'signature_image', $attach_id );
		}

		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		$attach_data = wp_generate_attachment_metadata( $attach_id, $path_name );
		wp_update_attachment_metadata( $attach_id , $attach_data );

		return $return;
	}

	/**
	 * Determines if a file is a valid PNG image. An exception
	 * is thrown the path is empty or if te path does not exist.
	 * @param $image_path
	 * @return bool
	 * @throws \InvalidArgumentException
	 * @throws \Exception
	 */
	protected function is_png( $image_path ) {
		if( empty( $image_path ) ) {
			throw new \InvalidArgumentException('Image path must be a string & not be empty');
		}

		if( ! file_exists( $image_path ) ) {
			throw new \Exception("Image path is not a valid path");
		}
		$image_data = getimagesize( $image_path );
		$return = true;

		if( array_key_exists( 'mime', $image_data ) && $image_data['mime'] != 'image/png' ) {
			$return = false;
		}

		return $return;
	}
}

?>