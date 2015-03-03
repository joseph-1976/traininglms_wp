<?php
namespace TheTrainingMangerLMS\Admin;

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

import('TheTrainingMangerLMS.Admin.AjaxActionHandler');

final class CourseBuilderAjaxHandler extends AjaxActionHandler {
const HANDLE = "course_builder";

	public static function getAjaxAction() { return ttp_lms_prefix(self::HANDLE); }
	public static function getAjaxMethods() {
		return array(
			'get_course' => array(__CLASS__, 'get_course_callback'),
			'update_course' => array(__CLASS__, 'update_course_callback'),
			'get_lesson' => array(__CLASS__, 'get_lesson_callback'),
			'new_lesson' => array(__CLASS__, 'new_lesson_callback'),
			'update_lesson' => array(__CLASS__, 'update_lesson_callback'),
			'edit_lesson_screen' => array(__CLASS__, 'edit_lesson_screen_callback'),
			'remove_lesson' => array(__CLASS__, 'remove_lesson_callback'),
			'update_lessons_order' => array(__CLASS__, 'update_lessons_order_callback'),
			'new_topic' => array(__CLASS__, 'new_topic_callback'),
			'get_topic' => array(__CLASS__, 'get_topic_callback'),
			'delete_topic' => array(__CLASS__, 'delete_topic_callback'),
			'update_topics_order' => array(__CLASS__, 'update_topics_order_callback'),
			'move_topic' => array(__CLASS__, 'move_topic_callback'),
		);
	}

/**
	Ajax Callback functions
**/
	public function get_course_callback() {
		if (!isset($_POST['data']['course_id']) || !ttp_lms_course_valid($_POST['data']['course_id']))
			static::returnStatusCode(400);

		$course = \TheTrainingMangerLMS\Course::instance($_POST['data']['course_id']);

		wp_send_json(array('success' => 'true', 'data' => array('course' => $course->serialize())));
	}

	public function update_course_callback() {
		if (!isset($_POST['data']['course_id']) || !ttp_lms_course_valid($_POST['data']['course_id'])
			|| !isset($_POST['data']['updates']))
			static::returnStatusCode(400);

		$course = \TheTrainingMangerLMS\Course::instance($_POST['data']['course_id']);
		//try {
			$course->update($_POST['data']['updates']);
		//} catch (\ValidationExceptionBundle veb) { success => 'false'... }

		wp_send_json(array('success' => 'true', 'data' => array('course' => $course->serialize())));
	}

	public function get_lesson_callback() {
		if (!isset($_POST['data']['lesson_id']) || !ttp_lms_lesson_valid($_POST['data']['lesson_id']))
			static::returnStatusCode(400);

		$lesson = \TheTrainingMangerLMS\Lesson::instance($_POST['data']['lesson_id']);

		wp_send_json(array('success' => 'true', 'data' => array('lesson' => $lesson->serialize())));
	}

	public function new_lesson_callback() {
		if (!isset($_POST['data']['course_id']) ||  !ttp_lms_course_valid($_POST['data']['course_id']))
			static::returnStatusCode(400);

		$course = \TheTrainingMangerLMS\Course::instance($_POST['data']['course_id']);
		// do we have the title?
		$parameters = array();
		if (isset($_POST['data']['lesson_title']))
				$parameters['title'] = $_POST['data']['lesson_title'];
		$lesson = \TheTrainingMangerLMS\OnlineLesson::create($parameters);
		$course->addLesson($lesson);

		wp_send_json(array('success' => 'true', 'data' => array('lesson' => $lesson->serialize())));
	}

	public function update_lesson_callback() {
		if (!isset($_POST['data']['lesson_id']) || !ttp_lms_lesson_valid($_POST['data']['lesson_id'])
			|| !isset($_POST['data']['updates']))
			static::returnStatusCode(400);

		$lesson = \TheTrainingMangerLMS\Lesson::instance($_POST['data']['lesson_id']);
		//try {
			$lesson->update($_POST['data']['updates']);
		//} catch (\ValidationExceptionBundle veb) { success => 'false'... }

		wp_send_json(array('success' => 'true', 'data' => array('lesson' => $lesson->serialize())));
	}

	public function course_view_screen_callback() {

	}

	public function course_edit_screen_callback() {

	}
	public function lesson_view_screen_callback() {

	}
	public function edit_lesson_screen_callback() {
		if (!isset($_POST['data']['lesson_id']) || !ttp_lms_lesson_valid($_POST['data']['lesson_id']))
			static::returnStatusCode(400);

		$lesson = \TheTrainingMangerLMS\Lesson::instance($_POST['data']['lesson_id']);

		static::returnTemplate(dirname(__FILE__) . '/content/lesson-edit-screen.php', array('lesson' => $lesson));

	}

	public function remove_lesson_callback() {
		// TODO: make sure user has edit capabilities for this course_id
		if (!isset($_POST['data']['course_id']) || !ttp_lms_course_valid($_POST['data']['course_id']) ||
			!isset($_POST['data']['lesson_id']) || !ttp_lms_lesson_valid($_POST['data']['lesson_id']))
			static::returnStatusCode(400);

		$course = \TheTrainingMangerLMS\Course::instance($_POST['data']['course_id']);
		$lesson = \TheTrainingMangerLMS\Lesson::instance($_POST['data']['lesson_id']);
		$course->removeLesson($lesson);

		foreach($lesson->getTopics() as $topic) {
			$course->addUnassignedTopic($topic);
		}

		// for now don't delete the lesson
		//\TheTrainingMangerLMS\Lesson::delete($_POST['data']['lesson_id']);

		wp_send_json(array('success' => 'true', 'data' => 'true'));
	}

	public function new_topic_callback() {
		if (!isset($_POST['data']['lesson_id']) || !ttp_lms_lesson_valid($_POST['data']['lesson_id']) 
			|| !isset($_POST['data']['topic_content_type']) || !in_array($_POST['data']['topic_content_type'], \TheTrainingMangerLMS\Topic::getContentTypes()))
			static::returnStatusCode(static::HTTP_BAD_REQUEST);

		$lesson = \TheTrainingMangerLMS\Lesson::instance($_POST['data']['lesson_id']);

		$parameters = array('content_type' => $_POST['data']['topic_content_type']);
		if (isset($_POST['data']['topic_title']))
				$parameters['title'] = $_POST['data']['topic_title'];

		$topic = \TheTrainingMangerLMS\Topic::create($parameters);
		$lesson->addTopic($topic);

		//wp_send_json(array('success' => 'true', 'data' => array('lesson_id' => $lesson->ID(), 'lesson_title' => $lesson->getTitle())));
		wp_send_json(array('success' => 'true', 'data' => array('topic' => $topic->serialize())));
	}

	public function get_topic_callback() {
		if (!isset($_POST['data']['topic_id']) || !ttp_lms_topic_valid($_POST['data']['topic_id']))
			static::returnStatusCode(400);

		$topic = \TheTrainingMangerLMS\Topic::instance($_POST['data']['topic_id']);

		wp_send_json(array('success' => 'true', 'data' => array('topic' => $topic->serialize())));
	}

	public function delete_topic_callback() {
		if (!isset($_POST['data']['topic_id']) || !ttp_lms_topic_valid($_POST['data']['topic_id']) ||
			!isset($_POST['data']['lesson_id']) || !ttp_lms_lesson_valid($_POST['data']['lesson_id']))
			static::returnStatusCode(400);

		$topic = \TheTrainingMangerLMS\Topic::instance($_POST['data']['topic_id']);
		$lesson = \TheTrainingMangerLMS\Lesson::instance($_POST['data']['lesson_id']);
		$lesson->removeTopic($topic);

		\TheTrainingMangerLMS\Topic::delete($_POST['data']['topic_id']);

		wp_send_json(array('success' => 'true', 'data' => 'true'));
	}

	public function update_lessons_order_callback() {
		if (!isset($_POST['data']['course_id']) || !ttp_lms_course_valid($_POST['data']['course_id']) ||
				!isset($_POST['data']['lesson_order']))
			static::returnStatusCode(400);
		// make sure all lessons are in course, as well
		$course_id = $_POST['data']['course_id'];
		$lessons_order = explode(',', $_POST['data']['lesson_order']);
		// hackish way to do this
		$lessons = get_post_meta( $course_id, ttp_lms_prefix('course_lessons'), true );
		if ((count(array_diff($lessons, $lessons_order)) != 0) || (count(array_diff($lessons_order, $lessons)) != 0)) {
			static::returnStatusCode(400);
		}
		// update lesson order
		update_post_meta( $course_id, ttp_lms_prefix('course_lessons'), $lessons_order );
		wp_send_json(array('success' => 'true', 'data' => 'true'));
	}

	public function update_topics_order_callback() {
		if (!isset($_POST['data']['lesson_id']) || !ttp_lms_lesson_valid($_POST['data']['lesson_id']) ||
				!isset($_POST['data']['topic_order']))
			static::returnStatusCode(400);
		// make sure all lessons are in course, as well
		$lesson_id = $_POST['data']['lesson_id'];
		$topics_order = explode(',', $_POST['data']['topic_order']);
		// hackish way to do this
		$topics = get_post_meta( $lesson_id, ttp_lms_prefix('lesson_topics'), true );
		if ((count(array_diff($topics, $topics_order)) != 0) || (count(array_diff($topics_order, $topics)) != 0)) {
			static::returnStatusCode(400);
		}
		// update topic order
		update_post_meta( $lesson_id, ttp_lms_prefix('lesson_topics'), $topics_order );
		wp_send_json(array('success' => 'true', 'data' => 'true'));
	}

	public function move_topic_callback() {
		if (!isset($_POST['data']['source_lesson_id']) || !(($_POST['data']['source_lesson_id'] == -1) || ttp_lms_lesson_valid($_POST['data']['source_lesson_id'])) ||
			!isset($_POST['data']['destination_lesson_id']) || !(($_POST['data']['destination_lesson_id'] == -1) || ttp_lms_lesson_valid($_POST['data']['destination_lesson_id'])) ||
			!isset($_POST['data']['topic_id']) || !ttp_lms_topic_valid($_POST['data']['topic_id']) ||
			!isset($_POST['data']['index']) || !is_integer($_POST['data']['index'] + 0))
			static::returnStatusCode(400);

		$unassigned = false;
		if (($_POST['data']['source_lesson_id'] == -1) || ($_POST['data']['destination_lesson_id'] == -1)) {
			$unassigned = true;
			if (!isset($_POST['data']['course_id']) || !ttp_lms_course_valid($_POST['data']['course_id']))
				static::returnStatusCode(400);
			$course = \TheTrainingMangerLMS\Course::instance($_POST['data']['course_id']);
		}

		if ($_POST['data']['source_lesson_id'] != -1)
			$source_lesson = \TheTrainingMangerLMS\Lesson::instance($_POST['data']['source_lesson_id']);
		if ($_POST['data']['destination_lesson_id'] != -1)
			$destination_lesson = \TheTrainingMangerLMS\Lesson::instance($_POST['data']['destination_lesson_id']);
		$topic = \TheTrainingMangerLMS\Topic::instance($_POST['data']['topic_id']);
		$index = $_POST['data']['index'] + 0;

		if (!$unassigned) {
			$source_lesson->removeTopic($topic);
			$destination_lesson->insertTopic($index, $topic);
		} else {
			if ($_POST['data']['source_lesson_id'] == -1) {
				// we're coming from unassigned topics
				$course->removeUnassignedTopic($topic);
				$destination_lesson->insertTopic($index, $topic);
			} else {
				// we're going to unassigned topics
				$source_lesson->removeTopic($topic);
				$course->addUnassignedTopic($topic);
			}
		}

		wp_send_json(array('success' => 'true', 'data' => 'true'));
	}

}

?>