<?php
namespace TheTrainingMangerLMS;

/**
 * Training LMS base Course class
 *
 * This class encapsulates fields and functionality
 * specific to Training LMS Course.
 *
 **/
import('TheTrainingMangerLMS.Course');

/*
	The class
*/
class OnlineCourse extends Course {
protected static $fields = array(
	'lesson_trainers' => array( 'source' => 'postmeta', 'default' => array() ),
	'regular_price' => array( 'source' => 'postmeta', 'default' => '1.00' ),
	'sale_price' => array( 'source' => 'postmeta', 'default' => '1.00' ),
);
	protected static function getFieldsDescriptor() {
		return array_replace(parent::getFieldsDescriptor(), self::$fields);
	}

/**
		Accessor methods
**/
	public function getRegularPrice() {
		return $this->regular_price;
	}

	public function getSalePrice() {
		return $this->sale_price;
	}

/**
	Official Trainer functions.
**/
	public function setLessonTrainer(Lesson $lesson, Trainer $trainer) {
		// add code to remove lesson as well
		// make sure is valid lesson
		$lessons = $this->lessons;
		$index = array_search($lesson->ID(), $lessons);
		if ($index === false)
			throw new \InvalidArgumentException("Lesson does not belong to this course.");
		$lessonTrainers = $this->lesson_trainers;
		//if (array_key_exists($lesson->ID(), $lessonTrainers))
		$lessonTrainers[$lesson->ID()] = $trainer->ID();
		$this->lesson_trainers = $lessonTrainers;
	}

	public function getLessonTrainer(Lesson $lesson) {
		$lessons = $this->lessons;
		$index = array_search($lesson->ID(), $lessons);
		if ($index === false)
			throw new \InvalidArgumentException("Lesson does not belong to this course.");
		$lessonTrainers = $this->lesson_trainers;
		if (!array_key_exists($lesson->ID(), $lessonTrainers)) return null;
		return Trainer::instance($lessonTrainers[$lesson->ID()]);
	}

	public function getAssociatedProduct() {
			return \TheTrainingMangerLMS\Utility::getAssociatedProduct('_course_id', $this->ID());
	}
}

?>