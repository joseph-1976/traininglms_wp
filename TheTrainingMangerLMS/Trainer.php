<?php
namespace TheTrainingMangerLMS;

import('TheTrainingMangerLMS.Constants');
import('TheTrainingMangerLMS.User');
//import('TheTrainingMangerLMS.TrainingCompany');  Do not activate this line due to circular reference
require_once "trainer_functions.php";

class Trainer Extends User {
	// A Role defines a set of tasks a user assigned the role is allowed to perform
	// A Capability is a permission to perform one or more types of task. 
	const ADMIN = 'tc_admin';
	const EDITOR = 'tc_editor';
	const TRAINER = 'tc_trainer';

	protected static function prefix($key) { return ttp_lms_prefix('trainer_' . $key); }

	protected static function getFieldsDescriptor() {
		return array_replace(parent::getFieldsDescriptor(), self::$fields);
	}

	protected static $fields = array(
		'description' => array( 'source' => 'usermeta','default' => '' ), // was bio
		'tagline' => array( 'source' => 'usermeta','default' => '' ),
		'photo' => array( 'source' => 'usermeta', 'default' => 0 ),
		'certifications' => array( 'source' => 'usermeta', 'default' => array() ),
//		'capability' => array( 'source' => 'usermeta', 'default' => self::EDITOR ), // Role is more indicative of Student/Trainer/Instructor
//		'current_tc' => array( 'source' => 'usermeta', 'default' => 0 ),
	);

//	private function _getAssociatedTCs_List() {
		/*"SELECT IFNULL(GROUP_CONCAT(p.ID SEPARATOR ','), '') FROM wp_posts p JOIN wp_postmeta m ON (p.ID = m.post_id) 
		WHERE p.post_type = ttp_lms_post_prefix(Constants::TrainingCompany) AND
		m.meta_key = 'ttp_lms_tc_trainers' AND m.metavalue REGEXP " . Utility::InSerializedArray($this->user_id);
		or get_results(ARRAY_N) and array_map check*/
//	}
//	public function getAssociatedTCs() {
//	}

	private function getPhoto() {
		$photo = $this->get( 'photo' );
		if (is_null($photo) || ($photo == 0)) return NULL;
		// FIXME: return url to an image
		return $this->get_meta_value( 'photo' );
	}

	private function set_photo( $file_path ) {
		// ?????????????????????????????????
		// I think this functionality needs to be in Utility
		// Utility::uploadImage( $file_path );
		$wp_upload_dir = wp_upload_dir();

		$file_info = pathinfo( $file_path );
		$extension = $file_info['extension'];

		$formatted_name = $this->user_id.'_photo'.$extension;
		$path_name = $wp_upload_dir['path'].'/'.$formatted_name;
		move_uploaded_file( $file_path, $path_name );

		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$mime_type = finfo_file($finfo, $path_name);
		$attachment = array(
			'guid'           => $wp_upload_dir['url'] . '/' . $formatted_name,
			'post_mime_type' => $mime_type,
			'post_title'     => $formatted_name,
			'post_content'   => '',
			'post_status'    => 'inherit'
		);

		$return = false;
		$attach_id = wp_insert_attachment( $attachment, $path_name, get_the_ID() );
		if( ! empty( $attach_id ) ) {
			$key = ttp_lms_prefix( 'trainer_photo' );
			$return = $this->set_meta_value( $key, $attach_id );
		}

		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		$attach_data = wp_generate_attachment_metadata( $attach_id, $path_name );
		wp_update_attachment_metadata( $attach_id , $attach_data );

		return $return;
	}

	public function getManagedCourses() {
	}

/*	public function getCurrentTC() {
		$current_tc = $this->get( 'current_tc' );
		if (($current_tc == '') || ($current_tc == 0)) return null;
		return \TheTrainingMangerLMS\TrainingCompany::instance($current_tc);
	}
	public function setCurrentTC(\TheTrainingMangerLMS\TrainingCompany $tc = null) {
		if (!is_null($tc)) {
			if (!in_array($tc->ID(), $this->_getAssociatedTCs_List()))
				throw new \InvalidArgumentException("The trainer is not a member of this training company.");
		}
		$this->set( 'current_tc', is_null($tc) ? 0 : $tc->ID() );
	}*/

	public function get_active_courses() {
		// we assume this means courses currently being managed
		return $this->get_current_courses();
	}

	public function get_num_active_courses() {
		$return = 0;
		$course_array = $this->get_active_courses();
		if( is_array( $course_array ) ) {
			$return = count($course_array);
		}

		return $return;
	}

	public function get_current_students() {
		$course_array = $this->get_current_courses();
		if( ! is_array( $course_array ) ) {
			return false;
		}

		$student_array = array();
		foreach( $course_array as $course_id ) {
			$course_obj = \TheTrainingMangerLMS\OnlineCourse::instance( $course_id );
			$student_array = array_merge( $student_array, $course_obj );
		}

		return $student_array;
	}

	public function get_earned_certifications() {
		return maybe_unserialize( get_user_meta($this->user_id, ttp_lms_prefix( 'completed_certifications' ), true) );
	}

	public function get_num_earned_certifications() {
		$result = $this->get_earned_certifications();
		if( ! is_array( $result ) ) {
			return false;
		}

		return count( $result );
	}

	public function render_display_for_selection_header() {

	}

	/*
	 * The following function are TODO
	 * until the required functionality is implemented
	 */
	public function get_s3_bucket() {
		return false;
	}

	public function get_past_students() {
		return false;
	}

	public function get_num_past_students() {
		return false;
	}

	public function get_num_all_students() {
	return false;
	}
}