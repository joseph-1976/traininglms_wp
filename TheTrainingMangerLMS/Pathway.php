<?php
namespace TheTrainingMangerLMS;

/**
 * Training LMS Pathway class
 *
 * This class encapsulates fields and functionality
 * specific to Training LMS Pathway type.
 *
 **/

import('TheTrainingMangerLMS.CommonCPTBase');

/*
 * The class
 */
class Pathway extends CommonCPTBase {

	public $goal;

	/**
	 * The constructor sets the post ID
	 * for the instance & defines what fields
	 * are associated with the instance type.
	 * The fields set are those that can be stored easily
	 * in metadata and those that require specific get/set methods.
	 * A list of all fields (for the sake of loops/verification) can
	 * be easily accessed by looping through the field_array attribute.
	 * All fields can be accessed/modified using simple get()/set()
	 * functions.
	 * @param bool $post_id
	 * @param bool $set_post
	 * @param null $goal
	 */
	function __construct( $post_id = false, $set_post = true, $goal = null ) {
		$this->meta_prefix = 'pathway_';

		$this->nonmeta_field_array = array(
			'title',
			'description_long',
			'main_image'
		);

		$this->meta_field_array = array(
			'description_short',
			'description_medium',
			'tagline',
			'presale_forum_id',
			'force_ordered_progression',
			'list_of_courses',
			'associated_goal_id'
		);

		$this->field_array = array_merge( $this->nonmeta_field_array, $this->meta_field_array );

		if( ! $post_id && $set_post ) {
			$this->post_id = get_the_ID();
		} else {
			$this->post_id = $post_id;
		}

		if( ! empty( $goal ) ) {
			$this->goal = $goal;
			$this->set( 'associated_goal_id', $this->goal->post_id );
		}
	}

	/**
	 * Returns the value of a Pathway attribute
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
	 * Sets the value of a Pathway attribute
	 * @param $field_name
	 * @param $value
	 * @return bool|mixed
	 */
	public function set( $field_name, $value ) {
		$result = $this->set_base( $field_name, $value );

		if( ! $result ) {
			$result = call_user_func_array( array( $this, 'set_'.$field_name ), array( $value ) );
		}

		return $result;
	}

	/**
	 * Returns a list of all courses in a given pathway
	 * @return bool|mixed
	 */
	public function get_courses_in_pathway() {
		return $this->get( 'list_of_courses' );
	}

	/**
	 * Assigns a pathway to a specified user. The key can be
	 * "assigned" or "completed".
	 * @param $user_id
	 * @param string $key
	 * @return mixed
	 */
	public function set_pathway_status( $user_id, $key = 'assigned' ) {
		$metakey = ttp_lms_prefix( $key.'_pathways' );
		$pathway_array = maybe_unserialize( get_user_meta($user_id, $metakey, true) );

		if( is_array( $pathway_array ) && false !== array_search( $this->post_id, $pathway_array ) ) {
			return false;
		}

		if( empty( $pathway_array ) ) {
			$pathway_array = array( $this->post_id );
		} else {
			$pathway_array[] = $this->post_id;
		}

		if( 'completed' == $key ) {
			$assigned_key = ttp_lms_prefix( 'assigned_pathways' );
			$assigned_pathway_array = maybe_unserialize( get_user_meta( $user_id, $assigned_key, true ) );
			if( is_array( $assigned_pathway_array ) ) {
				$assigned_index = array_search( $this->post_id, $assigned_pathway_array );
				if( $assigned_index ) {
					unset( $assigned_pathway_array[$assigned_index] );
					update_user_meta( $user_id, $assigned_key, $assigned_pathway_array );
				}
			}
		}

		return update_user_meta( $user_id, $metakey, $pathway_array );
	}

	/**
	 * Assigns a pathway to "Completed" for a user. This automatically
	 * moves the course from "Assigned" for the user to "Completed".
	 * @param $user_id
	 * @return mixed
	 */
	public function complete_pathway( $user_id ) {
		return $this->set_pathway_status( $user_id, 'completed' );
	}

	/**
	 * Internal method for getting pathways associated with a user
	 * @param $user_id
	 * @param $key
	 * @return mixed
	 */
	private function internal_get_pathways( $user_id, $key ) {
		$key = ttp_lms_prefix( $key.'_pathways' );
		return maybe_unserialize( get_user_meta($user_id, $key, true) );
	}

	/**
	 * Returns all completed pathways associated with a user
	 * @param $user_id
	 * @return mixed
	 */
	public function get_completed_pathways( $user_id ) {
		return $this->internal_get_pathways( $user_id, 'completed' );
	}

	/**
	 * Returns all assigned pathways associated with a user
	 * @param $user_id
	 * @return mixed
	 */
	public function get_assigned_pathways( $user_id ) {
		return $this->internal_get_pathways( $user_id, 'assigned' );
	}

	/**
	 * Returns all courses in an ordered progression pathway
	 * before the specified course
	 * @param $course_id
	 * @return array|bool
	 */
	public function get_pathway_prerequisites_for_course( $course_id ) {
		$forced_progression = $this->get('force_ordered_progression');

		if( ! $forced_progression ) {
			return false;
		}

		$course_array = $this->get_courses_in_pathway();
		if( empty( $course_array ) || ! is_array( $course_array ) ) {
			return false;
		}

		$array_key = array_search( $course_id, $course_array );
		if( ! $array_key ) {
			return false;
		}

		return array_slice( $course_array, 0, $array_key);
	}

	/**
	 * Fetches the first course of a pathway
	 * @return bool
	 */
	public function get_pathway_prerequisites() {
		$forced_progression = $this->get('force_ordered_progression');

		if( ! $forced_progression ) {
			return false;
		}

		$course_array = $this->get_courses_in_pathway();
		if( empty( $course_array ) || ! is_array( $course_array ) ) {
			return false;
		}

		$course_id = $course_array[0];
		$course_obj = \TheTrainingMangerLMS\OnlineCourse::instance( $course_id );
		$prereq_array = $course_obj->getPrerequisites();
		$return_array = array();
		foreach( $prereq_array as $prereq ) {
			$return_array[] = $prereq->ID();
		}

		return $return_array;
	}

	/**
	 * Returns all pathways that contain the specified course Id
	 * @param $course_id
	 * @return array|bool
	 */
	public function get_pathways_with_course( $course_id ) {
		$args = array(
			'post_type' => ttp_lms_post_prefix('pathway'),
			'posts_per_page' => -1
		);
		$query = new \WP_Query( $args );
		$results_array = array();
		if( $query->have_posts() ){
			while( $query->have_posts() ){
				$query->the_post();
				$post_id = get_the_ID();
				$course_array = get_post_meta( $post_id, ttp_lms_prefix( 'objective_list_of_courses' ),true );
				if( ! empty( $course_array ) && is_array( $course_array ) ) {
					$array_key = array_search( $course_id, $course_array );
					if( $array_key ) {
						$results_array[] = $post_id;
					}
				}
			}
		}

		if( empty( $results_array ) ) {
			return false;
		} else {
			return $results_array;
		}
	}
}