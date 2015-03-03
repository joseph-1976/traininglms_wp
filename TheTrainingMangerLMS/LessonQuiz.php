<?php
namespace TheTrainingMangerLMS;

/**
 * Training LMS LessonQuiz class
 *
 * This class encapsulates fields and functionality
 * specific to Training LMS Lesson Quiz.
 *
 **/
import('TheTrainingMangerLMS.Quiz');

/*
	The class
*/
class LessonQuiz extends Quiz {
protected static $fields = array(
	'associated_lesson' => array( 'source' => 'post', 'wp_name' => 'post_parent' ),
	'estimated_length' => array( 'source' => 'postmeta', 'default' => 0 )
);
protected static function getFieldsDescriptor() {
	return array_replace(parent::getFieldsDescriptor(), self::$fields);
}

	protected function _getRandomQuestionsList() {
		$number = $this->getOption('number_of_questions');
		if ($number == 0) $number = count($this->questions);
		return $this->getRandomQuestionsList($number);
	}

	public function setAssociatedLesson(Lesson $lesson) {
		$this->associated_lesson = $lesson->ID();
	}
	public function getAssociatedLesson() {
		return $this->associated_lesson == 0 ? NULL : Lesson::instance($this->associated_lesson);
	}

	public function setEstimatedLength($length) {
		if (!is_int($length) || ($length < 0))
			throw new \InvalidArgumentException("Estimated length must be an integer and greater than zero.");
		$this->estimated_length = $length;
	}
	public function getEstimatedLength() {
		return $this->estimated_length;
	}

}

?>