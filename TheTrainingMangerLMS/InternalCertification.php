<?php
namespace TheTrainingMangerLMS;

/**
 * Training LMS Internal Certification class
 *
 * This class encapsulates fields and functionality
 * specific to Training LMS Internal Certification type.
 *
 **/
import('TheTrainingMangerLMS.Certification');

/*
 * The class
 */
class InternalCertification extends Certification {

	/**
	 * The constructor sets the post ID
	 * for the instance & defines what fields
	 * are associated with the instance type
	 * @param bool $post_id
	 */
	function __construct( $post_id = false, $set_post = true ) {
		if( ! $post_id && $set_post ) {
			$this->post_id = get_the_ID();
		} else {
			$this->post_id = $post_id;
		}

		$this->nonmeta_field_array = array(
			'title',
			'signature_image',
			'description_long',
			'maintenance_time_interval',
			'ec_credits_needed',
		);

		$this->meta_field_array = array(
			'description_short',
			'description_medium',
			'tagline',
			'time_cert_is_active',
			'presale_forum_id',
			'associated_tc', // TODO: Must be an ID when set
			'renewal_process',
			'renewal_fees',
			'rep_name',
			'rep_role',
			'required_renewal_courses',
			'cat_id'
		);

		$this->field_array = array_merge( $this->nonmeta_field_array, $this->meta_field_array );

	}

	/**
	 * Returns the value of an Internal Certification object attribute
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
	 * Sets the value of an Internal Certification object attribute
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
	 * Returns the internal extra credits amount
	 * @return bool
	 */
	private function get_ec_credits_needed() {
		return $this->get_meta_value( 'ec_credits_needed' );
	}

	/**
	 * Sets the internal extra credits amount
	 * @param $value
	 * @return bool
	 */
	private function set_ec_credits_needed( $value ) {
		return $this->set_integer_meta_field( 'ec_credits_needed', $value );
	}

}