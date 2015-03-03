<?php
namespace TheTrainingMangerLMS;

/**
 * Training LMS CourseQuiz class
 *
 * This class encapsulates fields and functionality
 * specific to Training LMS Course Quiz.
 *
 **/
import('TheTrainingMangerLMS.Quiz');

/*
	The class
*/
class CourseQuiz extends Quiz {
protected static $fields = array(
	'associated_course' => array( 'source' => 'post', 'wp_name' => 'post_parent' ),
	'number_of_lesson_questions' => array( 'source' => 'postmeta', 'default' => 0 ),
);
protected static function getFieldsDescriptor() {
	return array_replace(parent::getFieldsDescriptor(), self::$fields);
}
protected static function createOptions( $id, $value ) {
	\TheTrainingMangerLMS\Quiz\QuizCourseOptions::create( $id, $value );
}
protected static function readOptions( $id ) {
	return \TheTrainingMangerLMS\Quiz\QuizCourseOptions::load( $id );
}

	public function getRandomQuestionsList( $parameters ) {
		$course_number = $parameters['course_number'];
		$lesson_number = $parameters['lesson_number'];
		$questions = parent::getRandomQuestionsList( $course_number );
		$course = Course::instance($this->associated_course);
		foreach($course->getLessons() as $lesson) {
			$quiz = $lesson->getQuiz();
			if (!is_null($quiz)) {
				$questions = array_merge($questions, $quiz->getRandomQuestionsList( $lesson_number ));
			}
		}
		return $questions;
	}

	protected function _getRandomQuestionsList() {
		$number = $this->getOption('number_of_questions');
		if ($number == 0) $number = count($this->questions);
		return $this->getRandomQuestionsList(array( 'course_number' => $number, 'lesson_number' => $this->getOption('number_of_lesson_questions')));
	}

	public function setAssociatedCourse(Course $course) {
		$this->associated_course = $course->ID();
	}
	public function getAssociatedCourse() {
		return $this->associated_course == 0 ? NULL : Course::instance($this->associated_course);
	}
}

?>