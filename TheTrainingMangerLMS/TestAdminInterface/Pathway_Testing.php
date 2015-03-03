<?php
namespace TheTrainingMangerLMS\TestAdminInterface;

import('TheTrainingMangerLMS.Pathway');

/**
 * Training LMS class Pathway Test
 *
 * This class encapsulates methods & attributes
 * needed for testing the Pathway class via the
 * custom post type interface found in the WP admin.
 *
 **/

/*
	The class
*/
class Pathway_Testing {

	private $field_info = array(
		'title' => array( 'title' => 'Title', 'needs_testing_field' => false ),
		'description_short' => array( 'title' => 'Short Description', 'needs_testing_field' => true ),
		'description_medium' => array( 'title' => 'Medium Description', 'needs_testing_field' => true ),
		'description_long' => array( 'title' => 'Long Description', 'needs_testing_field' => false ),
		'tagline' => array( 'title' => 'Tagline', 'needs_testing_field' => true ),
		'presale_forum_id' => array( 'title' => 'Presale forum ID', 'needs_testing_field' => true ),
		'main_image' => array( 'title' => 'Main image', 'needs_testing_field' => false ),
		'force_ordered_progression' => array( 'title' => 'Force Order of Progression (True or False)', 'needs_testing_field' => true ),
		'list_of_courses' => array( 'title' => 'List of Required Courses (Seperate With Commas)', 'needs_testing_field' => true ),
		'associated_goal_id' => array( 'title' => 'Associated Milestone or Certification', 'needs_testing_field' => true ),
	);

	private $current_field;

	/**
	 * Constructor for Pathway Testing class
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
			$instance = new Pathway_Testing();
		}
	}

	/**
	 * Registers action hooks needed for testing
	 */
	private function setup_actions() {
		add_action( 'save_post',  array( $this, 'save_post' ), 10, 1 );
		add_action( 'add_meta_boxes', array( $this, 'add_pathway_fields' ), 10, 1 );
	}

	/**
	 * Function that saves specific attributes needed by the Pathway
	 * class.
	 */
	public function save_post() {
		$this->pathway = new \TheTrainingMangerLMS\Pathway();
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
			return;
		}

		if( get_post_type() != ttp_lms_post_prefix(\TheTrainingMangerLMS\Constants::PathwayPostType) ) {
			return;
		}

		remove_action( 'save_post', array( $this, 'save_post' ) );
		foreach( $this->pathway->field_array as $field ) {

			if(  'title' == $field || 'description_long' == $field ) {
				continue;
			}

			if( 'list_of_courses' == $field ) {
				if( $this->test_has_value( $_POST[$field] ) ) {
					$value = explode( ',', $_POST[$field] );
					$this->pathway->set( $field, $value );
				}
				continue;
			}

			if( 'force_ordered_progression' == $field ) {
				if( $this->test_has_value( $_POST[$field] ) ) {
					$value = $_POST[$field];
					if( 'true' == strtolower( $value ) ) {
						$this->pathway->set( $field, true);
					} else {
						$this->pathway->set( $field, false );
					}
				}
				continue;
			}

			if( $this->test_has_value( $_POST[$field] ) ) {
				$this->pathway->set( $field, $_POST[$field] );
			}
		}
		add_action( 'save_post',  array( $this, 'save_post' ), 10, 1 );
	}

	/**
	 * Adds meta box code to display testing fields
	 */
	public function add_pathway_fields() {
		$this->pathway = new \TheTrainingMangerLMS\Pathway();
		$post_type = ttp_lms_post_prefix(\TheTrainingMangerLMS\Constants::PathwayPostType);
		if( 'publish' == get_post_status() ) {
			add_meta_box( 'display_box',
				'Certification Information',
				array($this, 'pathway_callback'),
				$post_type,
				'normal',
				'high'
			);
		}


		foreach( $this->pathway->field_array as $field  ) {
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

		echo '<label for="myplugin_new_field">';
		echo $this->field_info[$field_name]['title'];
		echo '</label> ';
		echo '<input type="text" id="'.$field_name.'" name="'.$field_name.'" value="" />';
	}

	/**
	 * Displays information about the post
	 */
	public function pathway_callback() {
		foreach( $this->pathway->field_array as $field ) {
			echo $this->field_info[$field]['title'].': ';

			if( 'main_image' == $field ) {
				echo '<br />';
				echo '<img src="'.wp_get_attachment_url($this->pathway->get( 'main_image' )).'" />';
				echo '<br /><br />';
				continue;
			}

			if( 'force_ordered_progression' == $field ) {
				$value = $this->pathway->get( $field );
				if( $value ) {
					echo 'True';
				} else {
					echo 'False';
				}
				echo '<br /><br />';
				continue;
			}

			if( 'list_of_courses' == $field ) {
				echo '<br />';
				print_r( $this->pathway->get( $field ) );
				echo '<br /><br />';
				continue;
			}

			echo $this->pathway->get( $field );
			echo '<br /><br />';
		}

		echo 'Courses Before Course Id 4 (If 4 Is In Our Test Array):<br />';
		$value = $this->pathway->get_pathway_prerequisites_for_course( 4 );
		if( $value ) {
			print_r( $value );
		}
		echo '<br />';


		$new_course_id = $this->get_class_with_prereqs();
		$course_array = $this->pathway->get('list_of_courses');
		$original_course_array = $course_array;
		if( false === array_search( $new_course_id, $course_array ) ) {
			if( $course_array ) {
				array_unshift( $course_array, $new_course_id );
			} else {
				$course_array = array( $new_course_id );
			}
			$this->pathway->set( 'list_of_courses', $course_array );
		}

		echo '<p><em>TESTING: We are automatically adding a course to the pathway that we know has a prereq
		to test the get_pathway_prerequisites() functionality. The course is removed once the information is displayed </em></p>';
		echo 'Prereqs needed for the first course (which has been auto-added for testing) is :<br />';
		print_r($this->pathway->get_pathway_prerequisites());
		echo '<br /><br />';
		echo 'Pathways that include Course Id 4:<br />';
		$value = $this->pathway->get_pathways_with_course( 4 );
		if( $value ) {
			print_r( $value );
		}
		echo '<br /><br />';
		echo '<p><em>TESTING: We add the course to the user\'s completed pathways first in testing BEFORE adding
		 it to the user\'s assigned pathways as assigning it to completed last would remove it from assigned.</em></p>';
		echo 'Current User\'s Completed Pathways:<br />';
		$this->pathway->set_pathway_status( get_current_user_id(), 'completed' );
		$value = $this->pathway->get_completed_pathways( get_current_user_id() );
		print_r( $value );
		echo '<br /><br />';
		echo 'Current User\'s Assigned Pathways:<br />';
		$this->pathway->set_pathway_status( get_current_user_id() );
		$value = $this->pathway->get_assigned_pathways( get_current_user_id() );
		print_r( $value );
		echo '<br /><br />';
		$this->pathway->set( 'list_of_courses', $original_course_array );
	}

	private function get_class_with_prereqs() {
		$args = array(
			'post_type' => ttp_lms_post_prefix(\TheTrainingMangerLMS\Constants::CoursePostType),
			'posts_per_page' => -1
		);
		$query = new \WP_Query( $args );
		$course_id = false;

		if( $query->have_posts() ){
			while( $query->have_posts() ){
				$query->the_post();
				$post_id = get_the_ID();
				try {
					$course = \TheTrainingMangerLMS\Course::instance( $post_id );
				} catch( \Exception $e ) {
					// We are just checking if an instance can be created. An exception
					// means it cannot. In testing, we don't need to know why it failed.
					continue;
				}

				$prereq_array = $course->getPrerequisites();

				if( ! is_array( $prereq_array ) || 0 == count( $prereq_array ) ) {
					continue;
				}

				$course_id = $post_id;
				break;
			}
		}

		if( ! $course_id  ) {
			$prereq =	\TheTrainingMangerLMS\OnlineCourse::create( array() );
			$main = \TheTrainingMangerLMS\OnlineCourse::create( array() );
			$main->addPrerequisite( $prereq );
			$course_id = $main->ID();
		}

		return $course_id;
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
}

Pathway_Testing::Instance();
?>