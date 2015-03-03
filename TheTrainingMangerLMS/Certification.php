<?php
namespace TheTrainingMangerLMS;

/**
 * Training LMS base Certification class
 *
 * This class encapsulates fields and functionality
 * specific to Training LMS Certificate classes.
 * This includes Internal Certifications &
 * External Certifications.
 *
 **/
import('TheTrainingMangerLMS.Goal');

/*
 * The class
 */
abstract class Certification extends Goal {

	/**
	 * Returns the maintenance time interval
	 * of a certification. If none is present, it
	 * will default to the amount of time that the
	 * cert is active and then to false
	 * @return bool|mixed
	 */
	protected function get_maintenance_time_interval() {
		$return = $this->get_base( 'maintenance_time_interval' );

		if( ! $return ) {
			$return = $this->get_base('time_cert_is_active');
		}

		return $return;
	}

	/**
	 * Sets the maintenance time interval
	 * @param $value
	 * @return bool
	 */
	protected  function set_maintenance_time_interval( $value ) {
		return $this->set_meta_value( 'maintenance_time_interval', $value );
	}

	/**
	 * Ensures a value is an integer then sets it in the post meta data
	 * @param $field_name
	 * @param $value
	 * @return bool
	 */
	protected function set_integer_meta_field( $field_name, $value ) {
		$value = (int) $value;
		if( ! is_int( $value ) ) {
			return false;
		}

		return $this->set_meta_value( $field_name, $value );
	}

	/**
	 * Returns the all certifications associated with a category
	 * as the results of a WP_Query object
	 * @param $cat_id
	 * @return WP_Query
	 */
	public static function getAllCertificationsByCategory( $cat_id ) {
		$post_types = array(
			ttp_lms_post_prefix(\TheTrainingMangerLMS\Constants::ExternalCertificationPostType),
			ttp_lms_post_prefix(\TheTrainingMangerLMS\Constants::InternalCertificationPostType),
		);
		$query_args = array(
			'post_type' => $post_types,
			'meta_query' => array(
				array(
					'key' => 'cat_id',
					'value' => $cat_id,
					'compare' => '=',
				)
			)
		);

		return new WP_Query( $query_args );
	}

	public function set_certification_status( $user_id, $key = 'assigned' ) {
		$metakey = ttp_lms_prefix( $key.'_certifications' );
		$certification_array = maybe_unserialize( get_user_meta($user_id, $metakey, true) );

		if( is_array( $certification_array ) && false !== array_search( $this->post_id, $certification_array ) ) {
			return false;
		}

		if( empty( $certification_array ) ) {
			$certification_array = array( $this->post_id );
		} else {
			$certification_array[] = $this->post_id;
		}

		if( 'completed' == $key ) {
			$assigned_key = ttp_lms_prefix( 'assigned_certifications' );
			$assigned_certification_array = maybe_unserialize( get_user_meta( $user_id, $assigned_key, true ) );
			if( is_array( $assigned_certification_array ) ) {
				$assigned_index = array_search( $this->post_id, $assigned_certification_array );
				if( $assigned_index ) {
					unset( $assigned_certification_array[$assigned_index] );
					update_user_meta( $user_id, $assigned_key, $assigned_certification_array );
				}
			}
		}

		return update_user_meta( $user_id, $metakey, $certification_array );
	}

}

?>