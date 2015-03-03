<?php
namespace TheTrainingMangerLMS\Quiz;

/**
 * Training LMS Quiz Options class
 *
 * This class encapsulates fields and functionality
 * specific to Training LMS Quiz Options.
 *
 **/
import('TheTrainingMangerLMS.Utility.PostOptions');

class QuizOptions extends \TheTrainingMangerLMS\Utility\PostOptions {
static $fields = array(
	'number_of_questions' => array('default' => 0, 'validation' => array('IsInteger', 'GreaterThanEqualZero')),
	'time_limit' => array('default' => 0, 'validation' => array('IsInteger', 'GreaterThanEqualZero')),
	'allowed_repeats' => array('default' => 0, 'validation' => array('IsInteger', 'GreaterThanEqualZero')),
	'pass_percentage' => array('default' => 80,),
	// delivery [mode] options
	'allow_back_button' => array('default' => 'false', 'validation' => array('IsBoolean', 'BooleanToText')),
	'all_questions_one_page' => array('default' => 'false', 'validation' => array('IsBoolean', 'BooleanToText')),
	'send_instructor_email' => array('default' => 'false', 'validation' => array('IsBoolean', 'BooleanToText')),
	'send_user_email' => array('default' => 'true',  'validation' => array('IsBoolean', 'BooleanToText')),
);
protected static function getFieldsDescriptor() {
	return self::$fields;
}
protected static function getDBOptionName() {
	return 'quiz_options';
}

}

class QuizQuestionOptions extends \TheTrainingMangerLMS\Utility\PostOptions {
static $fields = array(
	'provide_feedback' => array('default' => 'true', 'validation' => array('IsBoolean', 'BooleanToText')),
	'immediate_feedback' => array('default' => 'false', 'validation' => array('IsBoolean', 'BooleanToText')),
/*	'randomize_answers' => array('default' => 'true', 'validation' => array('IsBoolean', 'BooleanToText')),
	'alphabetize_answers' => array('default' => 'true', 'validation' => array('IsBoolean', 'BooleanToText')),*/
	'display_answer' => array('default' => 'false', 'validation' => array('IsBoolean', 'BooleanToText')),
	'display_points' => array('default' => 'false', 'validation' => array('IsBoolean', 'BooleanToText')),
	'display_category' => array('default' => 'false', 'validation' => array('IsBoolean', 'BooleanToText')),
);
protected static function getFieldsDescriptor() {
	return self::$fields;
}
protected static function getDBOptionName() {
	return 'quiz_question_options';
}

}

class QuizReportingOptions extends \TheTrainingMangerLMS\Utility\PostOptions {
static $fields = array(
	'show_score' => array('default' => 'true', 'validation' => array('IsBoolean', 'BooleanToText')),
	'show_quiz_time' => array('default' => 'true', 'validation' => array('IsBoolean', 'BooleanToText')),
	'show_average_point_score' => array('default' => 'false', 'validation' => array('IsBoolean', 'BooleanToText')),
	'show_category_score' => array('default' => 'false', 'validation' => array('IsBoolean', 'BooleanToText'))
);
protected static function getFieldsDescriptor() {
	return self::$fields;
}
protected static function getDBOptionName() {
	return 'quiz_reporting_options';
}

}

class QuizCourseOptions extends QuizOptions {
static $fields = array(
	'number_of_lesson_questions' => array('default' => 0, 'validation' => array('IsInteger', 'GreaterThanEqualZero')),
);
protected static function getFieldsDescriptor() {
	return array_replace(parent::getFieldsDescriptor(), self::$fields);
}

}

?>