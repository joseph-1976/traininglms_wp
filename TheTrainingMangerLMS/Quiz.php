<?php
namespace TheTrainingMangerLMS;

/**
 * Training LMS Quiz class
 *
 * This class encapsulates fields and functionality
 * specific to Training LMS Quiz.
 *
 **/
@import('WP_DB_Object');
import('TheTrainingMangerLMS.Constants');
import('TheTrainingMangerLMS.Quiz.QuizOptions');
import('TheTrainingMangerLMS.Quiz.Question');
import('TheTrainingMangerLMS.Student');

/*
	The class
*/
abstract class Quiz extends \WP_DB_Object {
protected static $fields = array(
	'title' => array( 'source' => 'post', 'wp_name' => 'post_title' ),
	'options' => array( 'source' => 'synthesized', 'synthesis' => array( 'create' => 'createOptions', 'read' => 'readOptions') ),
	// 'settings' => // technically can use options,
	'question_options' => array( 'source' => 'synthesized', 'synthesis' => array( 'create' => 'createQuestionOptions', 'read' => 'readQuestionOptions') ),
	'reporting_options' => array( 'source' => 'synthesized', 'synthesis' => array( 'create' => 'createReportingOptions', 'read' => 'readReportingOptions') ),
	'questions' => array( 'source' => 'postmeta', 'default' => array() )
);
protected static function getFieldsDescriptor() {
	return array_replace(parent::getFieldsDescriptor(), self::$fields);
}
protected static function prefix( $key ) {
	return ttp_lms_prefix('quiz_' . $key);
}
protected static function getPostType() {
	return ttp_lms_post_prefix(Constants::QuizPostType);
}

public static function create( $parameters, $options = array() ) {
	if (array_key_exists('options', $parameters)) {
		if (!is_array($parameters['options']))
			throw new \InvalidArgumentException("Parameter options must be an array.");
	} else {
		$parameters['options'] = array();
	}
	if (array_key_exists('question_options', $parameters) && !is_array($parameters['question_options'])) {
		throw new \InvalidArgumentException("Parameter question_options must be an array.");
	} else {
		$parameters['question_options'] = array();
	}
	if (array_key_exists('reporting_options', $parameters) && !is_array($parameters['reporting_options'])) {
		throw new \InvalidArgumentException("Parameter reporting_options must be an array.");
	} else {
		$parameters['reporting_options'] = array();
	}
/*	$parameters['options'] = array_key_exists('options', $parameters) ? array_replace($parameters['options'], \TheTrainingMangerLMS\Quiz\Options::defaults())
								: \TheTrainingMangerLMS\Quiz\Options::defaults();
	$parameters['question_options'] = array_key_exists('question_options', $parameters) ? array_replace($parameters['question_options'], \TheTrainingMangerLMS\Quiz\QuestionOptions::defaults())
								: \TheTrainingMangerLMS\Quiz\QuestionOptions::defaults();
	$parameters['reporting_options'] = array_key_exists('reporting_options', $parameters) ? array_replace($parameters['reporting_options'], \TheTrainingMangerLMS\Quiz\ReportingOptions::defaults())
								: \TheTrainingMangerLMS\Quiz\ReportingOptions::defaults();*/
	return parent::create( $parameters, $options );
}
protected static function createOptions( $id, $value ) {
	\TheTrainingMangerLMS\Quiz\QuizOptions::create( $id, $value );
}
protected static function readOptions( $id ) {
	return \TheTrainingMangerLMS\Quiz\QuizOptions::load( $id );
}
protected static function createQuestionOptions( $id, $value ) {
	\TheTrainingMangerLMS\Quiz\QuizQuestionOptions::create( $id, $value );
}
protected static function readQuestionOptions( $id ) {
	return \TheTrainingMangerLMS\Quiz\QuizQuestionOptions::load( $id );
}
protected static function createReportingOptions( $id, $value ) {
	\TheTrainingMangerLMS\Quiz\QuizReportingOptions::create( $id, $value );
}
protected static function readReportingOptions( $id ) {
	return \TheTrainingMangerLMS\Quiz\QuizReportingOptions::load( $id );
}

/**
	Options related functions
**/
	public function setOption($name, $value) {
		$this->options->setOption($name, $value);
		$this->options->update();
	}
	public function getOption($name) {
		return $this->options->getOption($name);
	}
	public function getOptions() {
		return $this->options;
	}

	public function setQuestionOption($name, $value) {
		$this->question_options->setOption($name, $value);
		$this->question_options->update();
	}
	public function getQuestionOption($name) {
		return $this->question_options->getOption($name);
	}
	public function getQuestionOptions() {
		return $this->question_options;
	}

	public function setReportingOption($name, $value) {
		$this->reporting_options->setOption($name, $value);
		$this->reporting_options->update();
	}
	public function getReportingOption($name) {
		return $this->reporting_options->getOption($name);
	}
	public function getReportingOptions() {
		return $this->reporting_options;
	}
//public function setSetting($name, $value);
//public function getSetting($name);

/**
	Question functions
**/
	/**
	 * Get the Questions for the Quiz.
	 *
	 * @see TheTrainingMangerLMS\Quiz\Question
	 *
	 * @return array of Question objects.
	 */
	public function getQuestions() {
		$questions = array();
		foreach($this->questions as $question_id) {
//			print("Question ID: " . $question_id . "\n");
//			$questionType = static::getPostObjectType( $question_id );
			array_push($questions, Quiz\Question::instance($question_id));
		}
		return $questions;
	}
	// Questions don't need order
	public function addQuestion(\TheTrainingMangerLMS\Quiz\Question $question) {
		if (in_array($question->ID(), $this->questions))
			throw new \InvalidArgumentException("Question is already added.");
		$temp = $this->questions;
		array_push($temp, $question->ID());
		$this->questions = $temp;
	}
	public function removeQuestion(\TheTrainingMangerLMS\Quiz\Question $question) {
		$index = array_search($question->ID(), $this->questions);
		if ($index === FALSE)
			throw new \InvalidArgumentException("Question is not a part of this quiz.");
		// slice it up
		$this->questions = array_merge(array_slice($this->questions, 0, $index), 
			array_slice($this->questions, $index+1, count($this->questions)-1));
	}

/*public function getRandomQuestion($exclude_list) {
	// validate exclude_list
	foreach($exclude_list as $question_id) {
		if (!in_array($question_id, $this->questions))
			throw new \InvalidArgumentException("Question ID " . $question_id . " is not a question in this quiz.");
	}
	// make sure exclude_list has unique ids
	$exclude_list = array_unique($exclude_list, SORT_NUMERIC);
	if ((count($this->questions) == 0) || (count($this->questions) == count($exclude_list))) return NULL;
	// probably a better way to do this, but for now
	$list = $this->questions;
	foreach($exclude_list as $question_id) {
		if (($index = array_search($question_id, $list)) !== FALSE) {
			array_merge(array_slice($list, 0, $index),
				array_slice($list, $index+1, count($list)-1));
		}
	}
	return Question::instance($list[rand(0, count($list)-1)]);
}*/

	public function getRandomQuestionsList( $number ) {
		if (!is_int($number) && ($number < 1))
			throw new \InvalidArgumentException("Number {$number} is not valid.");
		if ($number > count($this->questions))
			throw new \InvalidArgumentException("Request number of questions is greater than actual number of questions.");
		$list = $this->questions;
		$questions = array();
		for ($i = 0; $i < $number; $i++) {
			$index = rand(0, count($list) - 1);
			array_push($questions, $list[$index]);
			$list = array_merge(array_slice($list, 0, $index), array_slice($list, $index+1, count($list)-1));
		}
		return $questions;
	}

	abstract protected function _getRandomQuestionsList();

	/**
		User functions 
	**/
	private function isBeingTakenBy(Student $user) {
		$active = get_user_meta($user->ID(), ttp_lms_prefix('quiz_active'), false);
		return (count($active) == 1) && ($this->id == $active[0]);
		//return count(get_user_meta($user->ID(), ttp_lms_prefix('quiz_' . $this->id . '_starttime_utc'), false)) == 1;
	}
	public function getNumberOfTries(Student $user) {
		// check to make sure user has taken quiz
		if (!$user->hasTakenQuiz($this))
			return 0;
			//throw new \InvalidArgumentException("Student has not taken this quiz.");
		return (int)get_user_meta($user->ID(), ttp_lms_prefix('quiz_' . $this->id . '_tries'), true);
	}
	public function startQuiz(Student $user) {
		// TODO: access control via Course and Lesson?
		if ($user->isTakingQuiz() != NULL)
			throw new \InvalidArgumentException("Student is currently taking a quiz.");
		if (!$user->hasTakenQuiz($this)) {
			// set up
			add_user_meta($user->ID(), ttp_lms_prefix('quiz_' . $this->id), $this->id);
			add_user_meta($user->ID(), ttp_lms_prefix('quiz_' . $this->id . '_tries'), 0);
		}
		$tries = $this->getNumberOfTries($user);
		if ($tries > $this->getOption('allowed_repeats'))
			throw new \InvalidArgumentException("Student has reach maximum number of tries for this quiz.");

		// generate questions, save them
		$tries++;
		$questions = $this->_getRandomQuestionsList();
		add_user_meta($user->ID(), ttp_lms_prefix('quiz_' . $this->id . '_try_' . $tries . '_questions'), implode(',', $questions));

		// mark time user started quiz
		$startTime = new \DateTime();
		$startTime->setTimeZone(new \DateTimeZone('UTC'));
		add_user_meta($user->ID(), ttp_lms_prefix('quiz_' . $this->id . '_starttime_utc'), $startTime->format("Y-m-d H:i:s"));

		// mark this quiz as active
		add_user_meta($user->ID(), ttp_lms_prefix('quiz_active'), $this->id);
	}
	public function getNextQuestion(Student $user) {
		if (!$this->isBeingTakenBy($user))
			throw new \InvalidArgumentException("Student is not taking this quiz.");
		$tries = $this->getNumberOfTries($user) + 1;
		$questions = explode(',', get_user_meta($user->ID(), ttp_lms_prefix('quiz_' . $this->id . '_try_' . $tries . '_questions'), true));
		global $wpdb;
		$answered = $wpdb->get_var($wpdb->prepare(
			"SELECT IFNULL(group_concat(a.meta_value ORDER BY FIND_IN_SET(a.meta_value, qs.meta_value) SEPARATOR ','), '') FROM $wpdb->usermeta a
			JOIN $wpdb->usermeta qs USING(user_id)
			WHERE user_id = %d AND qs.meta_key = %s AND a.meta_key = CONCAT(%s, a.meta_value)",
			$user->ID(), ttp_lms_prefix('quiz_' . $this->id . '_try_' . $tries . '_questions'), ttp_lms_prefix('quiz_' . $this->id . '_try_' . $tries . '_question_')
		));
		$answered = $answered == '' ? array() : explode(',', $answered);
		$unanswered = array_diff($questions, $answered);
		return count($unanswered) == 0 ? NULL : \TheTrainingMangerLMS\Quiz\Question::instance(array_shift($unanswered));
	}
// convenience function
public function isQuestionCurrent(Student $user, Quiz\Question $question) {
	return $question->ID() == $this->getNextQuestion($user)->ID();
}
// convenience function
public function evaluateQuestionResponse(Quiz\Question $question, $response) {
	if (!$this->isBeingTakenBy($user))
		throw new \InvalidArgumentException("Student is not taking this quiz.");
	// TODO: should we check to see if question is in questions?
	return $question->getAnswer()->evaluate($response);
}

	public function saveQuestionResponseAndOutcome(Student $user, Quiz\Question $question, $response, $outcome) {
		if (!$this->isBeingTakenBy($user))
			throw new \InvalidArgumentException("Student is not taking this quiz.");
		$try = $this->getNumberOfTries($user) + 1;
		// mark that the user has answewd had this question
		add_user_meta( $user->ID(), ttp_lms_prefix('quiz_' . $this->id . '_try_' . $try . '_question_' . $question->ID()), $question->ID() );
		// save the outcome and score
		add_user_meta( $user->ID(), ttp_lms_prefix('quiz_' . $this->id . '_try_' . $try . '_question_' . $question->ID() . '_outcome'), $outcome['outcome'] );
		add_user_meta( $user->ID(), ttp_lms_prefix('quiz_' . $this->id . '_try_' . $try . '_question_' . $question->ID() . '_score'), $outcome['score'] );
		// store total possible ponts as well since the values could be changed later
		add_user_meta( $user->ID(), ttp_lms_prefix('quiz_' . $this->id . '_try_' . $try . '_question_' . $question->ID() . '_tpp'), $outcome['tpp'] );//$question->getTotalPossiblePoints() );
		// save the raw response for possible review
		add_user_meta( $user->ID(), ttp_lms_prefix('quiz_' . $this->id . '_try_' . $try . '_question_' . $question->ID() . '_response'), $response );
	}
	public function endQuiz(Student $user) {
		if (!$this->isBeingTakenBy($user))
			throw new \InvalidArgumentException("Student is not taking this quiz.");
		$seconds = $this->getUserTime($user);
		$startTime_db = get_user_meta($user->ID(), ttp_lms_prefix('quiz_' . $this->id . '_starttime_utc'), true);
		delete_user_meta($user->ID(), ttp_lms_prefix('quiz_' . $this->id . '_starttime_utc'));
		$tries = $this->getNumberOfTries($user) + 1;
		update_user_meta($user->ID(), ttp_lms_prefix('quiz_' . $this->id . '_tries'), $tries);
		delete_user_meta($user->ID(), ttp_lms_prefix('quiz_active'));
		add_user_meta($user->ID(), ttp_lms_prefix('quiz_' . $this->id . '_try_' . $tries . '_starttime_utc'), $startTime_db);
		add_user_meta($user->ID(), ttp_lms_prefix('quiz_' . $this->id . '_try_' . $tries . '_totaltime'), $seconds);
		// store tpp for quiz since user may not have finished all the questions in the time alloted
		$ttp = $this->getTotalPossiblePoints($user, $tries);
		add_user_meta($user->ID(), ttp_lms_prefix('quiz_' . $this->id . '_try_' . $tries . '_ttp'), $ttp);
		$total_score = $this->getScorePoints($user, $tries);
		add_user_meta($user->ID(), ttp_lms_prefix('quiz_' . $this->id . '_try_' . $tries . '_totalscore'), $total_score);
		// store passed or failed
		$passed = ($total_score / $ttp * 100) >= $this->getOption('pass_percentage');
		add_user_meta($user->ID(), ttp_lms_prefix('quiz_' . $this->id . '_try_' . $tries . '_passed'), $passed ? 'true' : 'false');
	}

	/**
		User time limit functions
	**/
	public function getUserTime(Student $user) {
		if (!$this->isBeingTakenBy($user))
			throw new \InvalidArgumentException("Student is not taking this quiz.");
		$startTime_db = get_user_meta($user->ID(), ttp_lms_prefix('quiz_' . $this->id . '_starttime_utc'), true);
		$startTime = \DateTime::createFromFormat("Y-m-d H:i:s", $startTime_db, new \DateTimeZone('UTC'));
		$seconds = (new \DateTime())->getTimestamp() - $startTime->getTimestamp();
		return $seconds;
	}

	public function hasUserTimeExpired(Student $user) {
		if (!$this->isBeingTakenBy($user))
			throw new \InvalidArgumentException("Student is not taking this quiz.");
		if ($this->getOption('time_limit') == 0) return false;
		return $this->getUserTime($user) >= $this->getOption('time_limit');
	}

	private function passed(Student $user, $try = 0) { // 0 for last time
		// validate user has taken quiz
		if (!$user->hasTakenQuiz($this))
			throw new \InvalidArgumentException("Student has not taken this quiz.");
		// validate try
		if (!is_integer($try) && ($try < 0))
			throw new \InvalidArgumentException("Invalid repeat value.");
		$tries = $this->getNumberOfTries($user);
		if ($try == 0) $try = $tries;
		elseif ($try > $tries)
			throw new \InvalidArgumentException("There is no such repeat of the quiz.");
		$total = $this->getTotalPossiblePoints($user, $try);
		$score = $this->getScorePoints($user, $try);
		return ($score / $total * 100) >= $this->getOption('pass_percentage');
	}

	public function getTotalPossiblePoints(Student $user, $try) {
		// validate user has taken quiz
		if (!$user->hasTakenQuiz($this))
			throw new \InvalidArgumentException("Student has not taken this quiz.");
		// validate try
		$tries = $this->getNumberOfTries($user);
		if ($try > $tries)
			throw new \InvalidArgumentException("There is no such repeat of the quiz.");
		global $wpdb;
		$query = $wpdb->prepare(
			"SELECT IFNULL(SUM(q.meta_value), 0) AS total FROM $wpdb->postmeta q
			WHERE q.meta_key = %s AND FIND_IN_SET(q.post_id, 
				(SELECT meta_value FROM $wpdb->usermeta WHERE user_id = %d AND meta_key = %s)
			)",
			ttp_lms_prefix('question_total_points'), $user->ID(), ttp_lms_prefix('quiz_' . $this->id . '_try_' . $try . '_questions')
			);
		return $wpdb->get_var($query);
	}
	public function getScorePoints(Student $user, $try) {
		// validate user has taken quiz
		if (!$user->hasTakenQuiz($this))
			throw new \InvalidArgumentException("Student has not taken this quiz.");
		// validate try
		$tries = $this->getNumberOfTries($user);
		if ($try > $tries)
			throw new \InvalidArgumentException("There is no such repeat of the quiz.");
		global $wpdb;
		$query = $wpdb->prepare(
			"SELECT IFNULL(SUM(s.meta_value), 0) AS total FROM $wpdb->usermeta s
			JOIN $wpdb->usermeta q USING (user_id)
			WHERE user_id = %d AND q.meta_key = CONCAT(%s, q.meta_value) AND FIND_IN_SET(q.meta_value,
				(SELECT meta_value FROM $wpdb->usermeta WHERE user_id = %d AND meta_key = %s)) 
				AND s.meta_key = CONCAT(q.meta_key, '_score')",
			$user->ID(), ttp_lms_prefix('quiz_' . $this->id . '_try_' . $try . '_question_'),
			$user->ID(), ttp_lms_prefix('quiz_' . $this->id . '_try_' . $try . '_questions')
			);
		return $wpdb->get_var($query);
	}

}

?>