<?php
namespace TheTrainingMangerLMS;

import('TheTrainingMangerLMS.Trainer');
import('TheTrainingMangerLMS.CommonCPTBase');

class TrainingCompany extends CommonCPTBase {

	const ADMIN = 'tc_admin';
	const EDITOR = 'tc_editor';
	const TRAINER = 'tc_trainer';

	function __construct( $post_id = false ) {
		$this->meta_prefix = 'training_company_';

		$this->nonmeta_field_array = array(
			'main_image',
			self::ADMIN,
			self::EDITOR,
			self::TRAINER
		);

		$this->meta_field_array = array(
			'description_short',
			'company_name',
			'address_1',
			'address_2',
			'city',
			'state',
			'zip',
			'name_for_checks',
			'certificate_id_prefix',
			'certificate_id_starting_number',
			'lesson_library',
			'certificate_issued',
			'lesson_library_tag_taxonomy',
			'lesson_library_category_taxonomy',
			'get_quiz_question_category_taxonomy',
			'quiz_question_id_array'
		);

		$this->field_array = array_merge( $this->nonmeta_field_array, $this->meta_field_array );

		if( ! $post_id ) {
			$this->post_id = get_the_ID();
		} else {
			$this->post_id = $post_id;
		}
	}

	/**
	 * Returns the value of a Training Company attribute
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
	 * Sets the value of a Training Company attribute
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

	public function getTrainers() {
		$trainers = array();
		foreach ( $this->trainers as $trainer ) {
			array_push($trainers, \TheTrainingMangerLMS\Trainer::instance($trainer));
		}
		return $trainers;
	}
	public function addTrainer(Trainer $trainer, $role) {
	/*how to save trainer and role
	separate table
	training company, trainer id, role*/
	}
/*	public function get_trainers() {
		$args = array( 'meta_key' => 'country', 'meta_compare' => 'EXISTS' );
		$user_query = new \WP_User_Query( $args );
		$result_array = array();
		if ( ! empty( $user_query->results ) ) {
			foreach ( $user_query->results as $user ) {
				$trainer = new \TheTrainingMangerLMS\Trainer( $user->ID );
				$company_array = $trainer->get('tc_associations');
				if( ! is_array( $company_array ) ) {
					return false;
				}
				$tc_index = array_search( $this->post_id, $company_array );
				if( false !== $tc_index ) {
					$result_array[] = $user->ID;
				}
			}
		} else {
			return false;
		}

		return $result_array;
	}*/

	public function get_current_students() {
		$trainer_array = $this->get_trainers();
		if( ! is_array( $trainer_array ) ) {
			return false;
		}

		$results_array = array();
		foreach( $trainer_array as $trainer_id ) {
			$trainer = new \TheTrainingMangerLMS\Trainer( $trainer_id );
			$student_array = $trainer->get_current_courses();

			if( ! is_array( $student_array ) ) {
				continue;
			}

			$results_array = array_merge( $results_array, $student_array );
		}

		if( empty( $results_array ) ) {
			return false;
		}

		return $results_array;
	}

	public function get_num_active_courses() {
		$trainer_array = $this->get_trainers();
		if( ! is_array( $trainer_array ) ) {
			return false;
		}

		$count = 0;
		foreach( $trainer_array as $trainer_id ) {
			$trainer = new \TheTrainingMangerLMS\Trainer( $trainer_id );
			$student_array = $trainer->get_num_active_courses();

			if( ! is_array( $student_array ) ) {
				continue;
			}

			$count += count( $student_array );
		}

		return $count;
	}

	public function has_certificate_been_issued() {
		return $this->get('certificate_issued');
	}

	public function can_change_certification_id() {
		return ! $this->get('certificate_issued');
	}

	public function set_certification_id_prefix( $value ) {
		if( ! $this->canChangeCertificationID() ) {
			return false;
		}

		return $this->set('certificate_id_prefix', $value);
	}

	public function set_certification_starting_number( $value ) {
		if( ! $this->canChangeCertificationID() ) {
			return false;
		}

		return $this->set('certificate_id_starting_number', $value);
	}

	public function getLessonLibraryTagTaxonomy() {
		$this->get('');
	}


	public function create_lesson_taxonomies() {
		$labels = array(
			'name'              => 'Quiz Categories',
			'singular_name'     => 'Quiz Category',
			'search_items'      => __( 'Search Quiz Categories' ),
			'all_items'         => __( 'All Quiz Categories' ),
			'parent_item'       => __( 'Parent Quiz Category' ),
			'parent_item_colon' => __( 'Parent Quiz Category:' ),
			'edit_item'         => __( 'Edit Quiz Category' ),
			'update_item'       => __( 'Update Quiz Category' ),
			'add_new_item'      => __( 'Add New Quiz Category' ),
			'new_item_name'     => __( 'New Quiz Category Name' ),
			'menu_name'         => __( 'Quiz Category' ),
		);

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'Category' ),
		);

		register_taxonomy( $this->post_id.'_tc_quiz_categories', array( 'post' ), $args );

		$labels = array(
			'name'              => 'Lesson Categories',
			'singular_name'     => 'Lesson Category',
			'search_items'      => __( 'Search Lesson Categories' ),
			'all_items'         => __( 'All Lesson Categories' ),
			'parent_item'       => __( 'Parent Lesson Category' ),
			'parent_item_colon' => __( 'Parent Lesson Category:' ),
			'edit_item'         => __( 'Edit Lesson Category' ),
			'update_item'       => __( 'Update Lesson Category' ),
			'add_new_item'      => __( 'Add New Lesson Category' ),
			'new_item_name'     => __( 'New Lesson Category Name' ),
			'menu_name'         => __( 'Lesson Category' ),
		);

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'Category' ),
		);

		register_taxonomy( $this->post_id.'_tc_lesson_categories', array( 'post' ), $args );

		$labels = array(
			'name'              => 'Tags',
			'singular_name'     => 'Tag',
			'search_items'      => __( 'Search Tags' ),
			'all_items'         => __( 'All Tags' ),
			'parent_item'       => __( 'Parent Tag' ),
			'parent_item_colon' => __( 'Parent Tag:' ),
			'edit_item'         => __( 'Edit Tag' ),
			'update_item'       => __( 'Update Tag' ),
			'add_new_item'      => __( 'Add New Tag' ),
			'new_item_name'     => __( 'New Tag Name' ),
			'menu_name'         => __( 'Tag' ),
		);

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'Tag' ),
		);

		return register_taxonomy( $this->post_id.'_tc_lesson_tags', array( 'post' ), $args );
	}

	public function get_lesson_library_tag_taxonomy() {
		return get_taxonomy( $this->post_id.'_tc_lesson_tags' );
	}

	public function get_lesson_library_category_taxonomy() {
		return get_taxonomy( $this->post_id.'_tc_lesson_categories' );
	}

	public function get_quiz_question_category_taxonomy() {
		return get_taxonomy( $this->post_id.'_tc_quiz_categories'  );
	}

	public function add_lesson_library_tag( $tag ) {
		return wp_insert_term( $tag, $this->post_id.'_tc_lesson_tags' );
	}

	public function add_lesson_library_category( $tag, $parent_id = false ) {
		$args = array();
		if( $parent_id ) {
			$args['parent'] = $parent_id;
		}
		return wp_insert_term( $tag, $this->post_id.'_tc_lesson_categories', $args );
	}

	public function add_quiz_question_category( $tag, $parent_id = false ) {
		$args = array();
		if( $parent_id ) {
			$args['parent'] = $parent_id;
		}
		return wp_insert_term( $tag, $this->post_id.'_tc_quiz_categories', $args );
	}

	private function delete_term( $term_id, $taxonomy ) {
		return wp_delete_term( $term_id, $taxonomy );
	}

	public function delete_lesson_libray_tag( $term_id ) {
		return $this->delete_term( $term_id, $this->post_id.'_tc_lesson_tags' );
	}

	public function delete_lesson_library_category( $term_id ) {
		return $this->delete_term( $term_id, $this->post_id.'_tc_lesson_categories' );
	}

	public function delete_quiz_question_category( $term_id ) {
		return $this->delete_term( $term_id, $this->post_id.'_tc_quiz_categories' );
	}

	private function get_taxonomy_terms( $taxonomy ) {
		return wp_list_categories( array(
				'taxonomy' => $taxonomy,
				'orderby' => 'name',
				'hierarchical' => false
			)
		);
	}

	public function get_lesson_library_tags() {
		return $this->get_taxonomy_terms( $this->post_id.'_tc_lesson_tags' );
	}

	public function get_lesson_library_categories() {
		return $this->get_taxonomy_terms( $this->post_id.'_tc_lesson_categories'  );
	}

	public function get_quiz_question_categories() {
		return $this->get_taxonomy_terms( $this->post_id.'_tc_quiz_categories' );
	}

	public function get_library_lessons_in_category( $cat_id ) {
		$post_type = ttp_lms_post_prefix(\TheTrainingMangerLMS\Constants::LessonPostType);
		$args = array(
			'post_type' => $post_type,
			'tax_query' => array(
				array(
					'taxonomy' => $this->post_id.'_tc_lesson_categories',
					'field'    => 'term_id',
					'terms'    => $cat_id,
				),
			),
		);

		return new \WP_Query( $args );
	}

	public function get_lesson_library_lessons() {
		$post_type = ttp_lms_post_prefix(\TheTrainingMangerLMS\Constants::LessonPostType);
		$args = array(
			'post_type' => $post_type,
			'tax_query' => array(
				array(
					'taxonomy' => $this->post_id.'_tc_lesson_categories',
				),
			),
		);

		return new \WP_Query( $args );
	}

	// NOTE: Quiz functionality will need to be implemented later on.
	// This section should remain commented out until relations amongst
	// quizes, topics, and lessons are resolved.
	/*public function get_quiz_questions_for_topic( $lesson_topic_id ) {
		$results_array = array();
		$lesson_query = new \WP_Query( array(
			'post_type' => ttp_lms_post_prefix(\TheTrainingMangerLMS\Constants::LessonPostType)
		) );

		$lesson_array = array();
		if ( $lesson_query->have_posts() ) {
			while ( $lesson_query->have_posts() ) {
				$lesson_query->the_post();
				$post_id = get_the_ID();
				$lesson_obj = \TheTrainingMangerLMS\Lesson::instance( $post_id );
				$lesson_topics = $lesson_obj->getTopics();
				foreach( $lesson_topics as $topic ) {
					if( $topic->ID() == $lesson_topic_id ) {
						$lesson_array[] = $lesson_obj;
					}
				}
			}
		}
		wp_reset_postdata();

		foreach( $lesson_array as $lesson_obj ) {
			$quiz = $lesson_obj->getQuiz();
			if( ! empty( $quiz ) ) {
				$quiz_question_array = $quiz->getQuestions();
				if( ! empty( $quiz_question_array ) ) {
					array_merge( $results_array, $quiz_question_array );
				}
			}
		}

		if( empty( $results_array ) ) {
			$results_array = false;
		}

		return $results_array;
	}

	public function get_quiz_questions_for_lesson( $lesson_id, $flat_array = false ) {
		$lesson_obj = \TheTrainingMangerLMS\Lesson::instance( $lesson_id );
		if( $flat_array ) {
			$quiz = $lesson_obj->getQuiz();
			$quiz_question_array = $quiz->getQuestions();
			if( empty( $quiz_question_array ) ) {
				return false;
			} else {
				return $quiz_question_array;
			}
		} else {
			$lesson_topics = $lesson_obj->getTopics();
			$return_array = array();
			foreach( $lesson_topics as $topic ) {
				$topic_id = $topic->ID();
				$return_array[$topic_id] = $this->get_quiz_questions_for_topic( $topic_id );
			}
			if( empty( $return_array ) ) {
				return false;
			} else {
				return $return_array;
			}
		}
	}*/

	public function add_lesson_to_library($lesson_name, $lesson_id, $category, $tags) {
		global $wpdb;
		$wpdb->update( $wpdb->posts, array( 'post_title' =>  $lesson_name ), array( 'ID' => $lesson_id ) );

		$term_array = $this->get('lesson_library');
		$term_array[] = $lesson_id;
		$this->set( 'lesson_library', $term_array );

		return $this->add_lesson_library_category( $tags, $category );
	}

	public function delete_lesson_from_library( $lesson_id ) {
		$lesson_array = $this->get('lesson_library');
		if( ! is_array( $lesson_array ) ) {
			return false;
		}

		if( array_key_exists(  $lesson_id, $lesson_array ) ) {
			unset( $lesson_array[$lesson_id] );
			return $this->set( 'lesson_library', $lesson_array );
		}

		return false;
	}

	public function lesson_id_exists( $lesson_id ) {
		$lesson_post = get_post( $lesson_id );
		if( is_null( $lesson_post ) ) {
			return false;
		}

		$post_type = get_post_type( $lesson_post );
		if( $post_type != ttp_lms_post_prefix(\TheTrainingMangerLMS\Constants::TopicPostType) ) {
			return false;
		}

		return true;
	}

	public function get_tc_admin() {

	}

	public function set_tc_admin() {

	}
}