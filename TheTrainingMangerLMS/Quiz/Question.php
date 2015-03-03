<?php
namespace TheTrainingMangerLMS\Quiz;
use TheTrainingMangerLMS\Constants as Constants;
/**
 * Training LMS Quiz Question class
 *
 * This class encapsulates fields and functionality
 * specific to Training LMS Quiz Question.
 *
 **/
@import('WP_DB_Object');
import('TheTrainingMangerLMS.Quiz.Answer');

class Question extends \WP_DB_Object {
protected static $fields = array(
	'title' => array( 'source' => 'post', 'wp_name' => 'post_title' ),
	'text' => array( 'source' => 'post', 'wp_name' => 'post_content' ),
	//X'points_from_answer' => array( 'source' => 'postmeta', 'default' => 'false' ),
	'category' => array( 'source' => 'postmeta', 'default' => 0 ), // 'type' => 'ID'
	'answer' => array( 'source' => 'synthesized', 'synthesis' => array( 'create' => 'persistAnswer', 
				'persist' => 'persistAnswer', 'read' => 'readAnswer'), 'default' => NULL ), // complex type, use simple objects for easy serialization
	'feedback' => array( 'source' => 'postmeta', 'default' => '' ),
	'hint_mode' => array( 'source' => 'postmeta', 'default' => 'no_hint', 'validation' => array('inSet' => array('no_hint', 'hint_half', 'hint_full')) ),
	'hint'     => array( 'source' => 'postmeta', 'default' => '' )
);
protected static function getFieldsDescriptor() {
	return array_replace(parent::getFieldsDescriptor(), static::$fields);
}
protected static function prefix( $key ) {
	return ttp_lms_prefix('question_' . $key);
}
protected static function getPostType() {
	return ttp_lms_post_prefix(Constants::QuestionPostType);
}
static $answerTypes = array( );
static function getAnswerTypes() {
	return static::$answerTypes;
}

	protected static function persistAnswer($id, $value) {
		$answer = $value;
		update_post_meta($id, self::prefix('total_points'), is_null($answer) ? '' : $answer->getTotalPossiblePoints());
		update_post_meta($id, self::prefix('answer_type'), is_null($answer) ? '' : $answer->getType());
		update_post_meta($id, self::prefix('answer'), is_null($answer) ? '' : $answer);
	}
	protected static function readAnswer($id) {
		return get_post_meta($id, self::prefix('answer'), true);
	}

	public function setCategory( $category_id ) {
		// TODO: Needs to feed into lesson?
		//if (!is_valid_category($category_id))
		//	throw new \InvalidArgumentException("");
		$this->category = $category_id;
	}
	public function getCategory() {
		return $this->category;
	}

	public function setAnswer(Answer $answer) {
		$this->answer = $answer;
	}
	public function getAnswer() {
		return $this->answer;
	}

	// The answer sets the type for the question
	public function getType() {
		if (is_null($this->answer) || ($this->answer == ''))
			throw new \RuntimeException("Answer has not been set for this question.");
		return $this->answer->getType();
	}

	public function getTotalPossiblePoints() {
		if (is_null($this->answer) || ($this->answer == ''))
			throw new \RuntimeException("Answer has not been set for this question.");
		return $this->answer->getTotalPossiblePoints();
	}

	/**
	 * Evaluate and score the user's response.
	 *
	 * This function evaluates the users response for the question by calling the
	 * evaluate method of the set answer.
	 *
	 * @param array $response {
	 *      @type array answer An array containing the user's answer in the appropriate
	 *                         format based on the answer type.
	 *      @type bool used_hint Optional.  Indicates whether the user used the hint in
	 *                           answering the question.  Either 'true' or 'false'.
	 *                           Default is 'false'.
	 * }
	 * @return array An array containing the outcome and score.
	 */
	public function evaluate($response) {
		// ressponse = (timespent, used_hint, answer)
		if (is_null($this->answer) || ($this->answer == ''))
			throw new \RuntimeException("Answer has not been set for this question.");
		// TODO: validate $response
		return $this->answer->evaluate($response);
		// TODO: modify score depending on used_hint
	}

}

?>