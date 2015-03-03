<?php
namespace TheTrainingMangerLMS;

/**
 * Training LMS Milestone class
 *
 * This class encapsulates fields and functionality
 * specific to Training LMS Milestone type.
 *
 **/
import('TheTrainingMangerLMS.Goal');

/*
 * The class
 */
class Milestone extends Goal {

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
			'main_image',
			'signature_image',
			'description_long'
		);

		$this->meta_field_array = array(
			'description_short',
			'description_medium',
			'tagline',
			'associated_tc',
			'presale_forum_id',
			'cat_id'
		);

		$this->field_array = array_merge( $this->nonmeta_field_array, $this->meta_field_array );

	}

	/**
	 * Returns the attribute of a Milestone object
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
	 * Sets the attribute of a Milestone object
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
	 * Static method designed to return a WP Query object
	 * containing all Milestones that are associated with a category
	 * @param $cat_id
	 * @return WP_Query
	 */
	public static function get_all_milestones_by_category( $cat_id ) {
		$query_args = array(
			'post_type' => ttp_lms_post_prefix(\TheTrainingMangerLMS\Constants::MilestonePostType),
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
}
?>