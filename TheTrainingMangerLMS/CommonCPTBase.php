<?php
namespace TheTrainingMangerLMS;

/**
 * Class CommonCPTBase
 * This class encapsulates functionality for get/set
 * methods & attributes that apply to specific custom
 * post type classes including Pathways, Milestones,
 * Internal Certifications, & External Certifications
 * @package TheTrainingMangerLMS
 */

/*
 * The class
 */
abstract class CommonCPTBase {
	protected $post_id;
	protected $meta_field_array;
	protected $nonmeta_field_array;
	public $field_array;

	protected $meta_prefix = "objective_";

	abstract public function get( $field_name );
	abstract public function set( $field_name, $value );

	/**
	 * Returns the value of a field given the field name. If
	 * an error occurs or the field does not exist, then false
	 * is returned.
	 * @param $field_name
	 * @return bool|mixed
	 */
	protected function get_base( $field_name ) {
		$return = false;

		if( false !== in_array( $field_name, $this->meta_field_array ) ) {
			$return = $this->get_meta_value( $field_name );

			if( empty( $return ) ) {
				$return = false;
			}
		}

		return $return;
	}

	/**
	 * Sets the value of a field given the field name
	 * and the value for the field
	 * @param $field_name
	 * @param $value
	 * @return bool
	 */
	public function set_base( $field_name, $value ) {
		$return = false;

		if( false !== in_array( $field_name, $this->meta_field_array ) ) {
			$return = $this->set_meta_value( $field_name, $value );
		}

		return $return;
	}

	/**
	 * Checks if the field is a valid field for the post type
	 * @param $field_name
	 * @return bool
	 */
	public function is_field( $field_name ) {
		return array_key_exists( $field_name, $this->field_array );
	}


	/**
	 * Returns the value of a field stored in the post
	 * meta data. If the field does not exist then false
	 * is returned.
	 * @param $field_name
	 * @return bool
	 */
	protected function get_meta_value( $field_name ) {
		$field_name = ttp_lms_prefix($this->meta_prefix.$field_name);
		$field_value = get_post_meta( $this->post_id, $field_name, true );
		$return_value = false;
		if( ! empty( $field_value ) ) {
			$return_value = maybe_unserialize( $field_value );
		}

		return $return_value;
	}

	/**
	 * Sets the value of a field stored in the post's
	 * meta data
	 * @param $field_name
	 * @param $value
	 * @return bool
	 */
	protected function set_meta_value( $field_name, $value ) {
		$field_name = ttp_lms_prefix($this->meta_prefix.$field_name);
		return add_post_meta( $this->post_id, $field_name, $value, true ) || update_post_meta( $this->post_id, $field_name, $value );
	}

	/**
	 * Returns the title of the Goal
	 * @return mixed
	 */
	protected function get_title() {
		return get_the_title( $this->post_id );
	}

	/**
	 * Sets the title of the Goal
	 * @param $value
	 * @return mixed
	 */
	protected function set_title( $value ) {
		return wp_update_post(
			array (
				'ID'            => $this->post_id,
				'post_title'    => $value
			));
	}

	/**
	 * Gets the long description
	 * @return mixed
	 */
	protected function get_description_long() {
		$post =  get_post( $this->post_id );
		return $post->post_content;
	}

	/**
	 * Sets the long description
	 * @param $value
	 * @return mixed
	 */
	protected function set_description_long( $value ) {
		return wp_update_post(
			array (
				'ID'            => $this->post_id,
				'post_content'    => $value
			));
	}

	/**
	 * Return the main image for the instance
	 * @return mixed
	 */
	public function get_main_image() {
		return get_post_thumbnail_id( $this->post_id );
	}

	/**
	 * Sets the main image for the instance
	 * @param $thumbnail_id
	 * @return mixed
	 */
	public function set_main_image( $thumbnail_id ) {
		return set_post_thumbnail( $this->post_id, $thumbnail_id );
	}

}