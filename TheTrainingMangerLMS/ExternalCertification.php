<?php
namespace TheTrainingMangerLMS;

/**
 * Training LMS External Certification class
 *
 * This class encapsulates fields and functionality
 * specific to Training LMS External Certification type.
 *
 **/
import('TheTrainingMangerLMS.Certification');

/*
 * The class
 */
class ExternalCertification extends Certification {

	/**
	 * The constructor sets the post ID
	 * for the instance & defines what fields
	 * are associated with the instance type
	 * @param bool $post_id
	 * @param bool $set_post
	 */
	function __construct( $post_id = false, $set_post = true ) {
		if( ! $post_id && $set_post ) {
			$this->post_id = get_the_ID();
		} else {
			$this->post_id = $post_id;
		}

		$this->nonmeta_field_array = array(
			'title',
			'description_long',
			'maintenance_time_interval',
			'ec_amount',
		);

		$this->meta_field_array = array(
			'time_cert_is_active',
			'description_short',
			'description_medium',
			'tagline',
			'presale_forum_id',
			'ec_id',  // TODO: Must be an ID when set
			'designation',
			'cat_id'
		);

		$this->field_array = array_merge( $this->nonmeta_field_array, $this->meta_field_array );

	}

	/**
	 * Returns an attrobite of an External Certification object
	 * @param $field_name
	 * @return bool|mixed
	 */
	public function get( $field_name ) {
		$result = $this->get_base( $field_name );
		if( ! $result && ! in_array( $field_name, $this->meta_field_array ) ) {
			$result = call_user_func_array( array( $this, 'get_'.$field_name ), array() );
		}

		return $result;
	}

	/**
	 * Sets an attribute of an External Certification object
	 * @param $field_name
	 * @param $value
	 * @return bool|mixed
	 */
	public function set( $field_name, $value ) {
		$result = $this->set_base( $field_name, $value );

		if( ! $result && ! in_array( $field_name, $this->meta_field_array ) ) {
			$result = call_user_func_array( array( $this, 'set_'.$field_name ), array( $value ) );
		}

		return $result;
	}

	/**
	 * Returns the external extra credits value
	 * @return bool
	 */
	private function get_ec_amount() {
		return $this->get_meta_value( 'ec_amount' );
	}

	/**
	 * Sets the external extra credits valur
	 * @param $value
	 * @return bool
	 */
	private function set_ec_amount( $value ) {
		return $this->set_integer_meta_field( 'ec_amount', $value );
	}
}