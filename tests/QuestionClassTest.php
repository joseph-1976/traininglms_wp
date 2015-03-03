<?php
use TheTrainingMangerLMS\Quiz\Answer as Answer;

class QuestionClassTest extends WP_UnitTestCase
{

	public function test_simpleChoiceAnswer() {
		$choices = array();
		$parameters =  array( 'content' => "Richmond", 'content_type' => "text/plain", 'correct' => true, 'points' => 1 );
		array_push($choices, new TheTrainingMangerLMS\Quiz\SingleChoice($parameters));
		$choice = new TheTrainingMangerLMS\Quiz\SingleChoice();
		$choice->content = "New Orleans"; $choice->content_type = "text/plain"; $choice->correct = false; $choice->points = 1;
		array_push($choices, $choice);
		$choice = new TheTrainingMangerLMS\Quiz\SingleChoice();
		$choice->content = "Houston"; $choice->content_type = "text/plain"; $choice->correct = false; $choice->points = 1;
		array_push($choices, $choice);
		$choice = new TheTrainingMangerLMS\Quiz\SingleChoice();
		$choice->content = "Boston"; $choice->content_type = "text/plain"; $choice->correct = false; $choice->points = 1;
		array_push($choices, $choice);
		$singleChoice = new TheTrainingMangerLMS\Quiz\SingleChoiceAnswer($choices);
		$this->assertEquals(4, $singleChoice->getTotalPossiblePoints());
		$question = TheTrainingMangerLMS\Quiz\Question::create(
			array( 'title' => 'This is a simple choice question',
				   'text' => 'What is the Capitol of The State of Virginia?',
				   'answer' => $singleChoice
			) 
		);
		$this->assertTrue(get_class($question) == 'TheTrainingMangerLMS\Quiz\Question');
		$response = array( 'answer' => array(hash(Answer::HASH_ALGO, "Richmond")));
		$result = $question->evaluate($response);
		$this->assertEquals(4, $result['score']);
		$this->assertEquals(TheTrainingMangerLMS\Quiz\SingleChoiceAnswer::CORRECT, $result['outcome']);
		$response = array( 'answer' => array(hash(Answer::HASH_ALGO, "Houston")));
		$result = $question->evaluate($response);
		$this->assertEquals(2, $result['score']);
		$this->assertEquals(TheTrainingMangerLMS\Quiz\SingleChoiceAnswer::INCORRECT, $result['outcome']);
	}

	public function test_multipleChoiceAnswer() {
		$choices = array();
		$parameters = array('content' => "Summer", 'content_type' => "text/plain", 'correct' => true, 'points' => 1);
		array_push($choices, new TheTrainingMangerLMS\Quiz\MultipleChoice($parameters));
		$choice = new TheTrainingMangerLMS\Quiz\MultipleChoice();
		$choice->content = "Fall"; $choice->content_type = "text/plain"; $choice->correct = true; $choice->points = 1;
		array_push($choices, $choice);
		$choice = new TheTrainingMangerLMS\Quiz\MultipleChoice();
		$choice->content = "Winter"; $choice->content_type = "text/plain"; $choice->correct = true; $choice->points = 1;
		array_push($choices, $choice);
		$choice = new TheTrainingMangerLMS\Quiz\MultipleChoice();
		$choice->content = "Coffee"; $choice->content_type = "text/plain"; $choice->correct = false; $choice->points = 1;
		array_push($choices, $choice);
		$multipleChoice = new TheTrainingMangerLMS\Quiz\MultipleChoiceAnswer($choices);
		$this->assertEquals(4, $multipleChoice->getTotalPossiblePoints());
		$question = TheTrainingMangerLMS\Quiz\Question::create(
			array( 'title' => 'This is a multiple choice question',
				   'text' => 'What are the seasons of the year?',
				   'answer' => $multipleChoice
			) 
		);
		$this->assertTrue(get_class($question) == 'TheTrainingMangerLMS\Quiz\Question');
		$response = array( 'answer' => array(hash(Answer::HASH_ALGO, "Winter"), hash(Answer::HASH_ALGO, "Summer")));
		$result = $question->evaluate($response);
		$this->assertEquals(3, $result['score']);
		$this->assertEquals(TheTrainingMangerLMS\Quiz\SingleChoiceAnswer::PARTIALLY_CORRECT, $result['outcome']);
		$response = array( 'answer' => array(hash(Answer::HASH_ALGO, "Fall"), hash(Answer::HASH_ALGO, "Coffee")));
		$result = $question->evaluate($response);
		$this->assertEquals(1, $result['score']);
		$this->assertEquals(TheTrainingMangerLMS\Quiz\SingleChoiceAnswer::PARTIALLY_CORRECT, $result['outcome']);
	}

	public function test_freeChoiceAnswer() {
		$choices = array();
		$parameters = array('content' => "Purple", 'content_type' => "text/plain", 'points' => 4);
		array_push($choices, new TheTrainingMangerLMS\Quiz\FreeChoice($parameters));
		$choice = new TheTrainingMangerLMS\Quiz\FreeChoice();
		$choice->content = "Blue"; $choice->content_type = "text/plain"; $choice->points = 1;
		array_push($choices, $choice);
		$choice = new TheTrainingMangerLMS\Quiz\FreeChoice();
		$choice->content = "Yellow"; $choice->content_type = "text/plain"; $choice->points = 2;
		array_push($choices, $choice);
		$choice = new TheTrainingMangerLMS\Quiz\FreeChoice();
		$choice->content = "Red"; $choice->content_type = "text/plain"; $choice->points = 3;
		array_push($choices, $choice);
		$freeChoice = new TheTrainingMangerLMS\Quiz\FreeChoiceAnswer($choices);
		$this->assertEquals(10, $freeChoice->getTotalPossiblePoints());
		$question = TheTrainingMangerLMS\Quiz\Question::create(
			array( 'title' => 'This is a free choice question',
				   'text' => 'What are your favorite colors?',
				   'answer' => $freeChoice
			) 
		);
		$this->assertTrue(get_class($question) == 'TheTrainingMangerLMS\Quiz\Question');
		$response = array( 'answer' => array(hash(Answer::HASH_ALGO, "Purple"), hash(Answer::HASH_ALGO, "Red")));
		$result = $question->evaluate($response);
		$this->assertEquals(7, $result['score']);
		$this->assertEquals(TheTrainingMangerLMS\Quiz\SingleChoiceAnswer::NOT_APPLICABLE, $result['outcome']);
		$response = array( 'answer' => array());
		$result = $question->evaluate($response);
		$this->assertEquals(0, $result['score']);
	}

	public function test_sortingAnswer() {
		$choices = array();
		$parameters = array('content' => "1", 'content_type' => "text/plain", 'points' => 1);
		array_push($choices, new TheTrainingMangerLMS\Quiz\SortingChoice($parameters));
		$choice = new TheTrainingMangerLMS\Quiz\SortingChoice();
		$choice->content = "2"; $choice->content_type = "text/plain"; $choice->points = 1;
		array_push($choices, $choice);
		$choice = new TheTrainingMangerLMS\Quiz\SortingChoice();
		$choice->content = "3"; $choice->content_type = "text/plain"; $choice->points = 1;
		array_push($choices, $choice);
		$choice = new TheTrainingMangerLMS\Quiz\SortingChoice();
		$choice->content = "4"; $choice->content_type = "text/plain"; $choice->points = 1;
		array_push($choices, $choice);
		$sorting = new TheTrainingMangerLMS\Quiz\SortingAnswer($choices);
		$this->assertEquals(4, $sorting->getTotalPossiblePoints());
		$question = TheTrainingMangerLMS\Quiz\Question::create(
			array( 'title' => 'This is a sorting question',
				   'text' => 'Put the following numbers in order from smallest to largest',
				   'answer' => $sorting
			) 
		);
		$this->assertTrue(get_class($question) == 'TheTrainingMangerLMS\Quiz\Question');
		$response = array( 'answer' => array(hash(Answer::HASH_ALGO, "1"), hash(Answer::HASH_ALGO, "2"), hash(Answer::HASH_ALGO, "3"), hash(Answer::HASH_ALGO, "4")));
		$result = $question->evaluate($response);
		$this->assertEquals(4, $result['score']);
		$this->assertEquals(TheTrainingMangerLMS\Quiz\SingleChoiceAnswer::CORRECT, $result['outcome']);
		$response = array( 'answer' => array(hash(Answer::HASH_ALGO, "1"), hash(Answer::HASH_ALGO, "2"), hash(Answer::HASH_ALGO, "4"), hash(Answer::HASH_ALGO, "3")));
		$result = $question->evaluate($response);
		$this->assertEquals(2, $result['score']);
		$this->assertEquals(TheTrainingMangerLMS\Quiz\SingleChoiceAnswer::PARTIALLY_CORRECT, $result['outcome']);
	}

	public function test_AssessmentAnswer() {
		$choice = new TheTrainingMangerLMS\Quiz\AssessmentChoice();
		$choice->scale_values = array("1", "2", "3", "4", "5");//"Strongly Disagree", "Disagree", "Neutral", "Agree", "Strongly Agree");
		$choice->left_label = "Strongly Disagree";
		$choice->right_label = "Strongly Agree";
		$assessment = new TheTrainingMangerLMS\Quiz\AssessmentAnswer($choice);
		$this->assertEquals(5, $assessment->getTotalPossiblePoints());
		$question = TheTrainingMangerLMS\Quiz\Question::create(
			array( 'title' => 'This is an assessment question',
				   'text' => 'I feel the economy is on the right track.',
				   'answer' => $assessment
			) 
		);
		$this->assertTrue(get_class($question) == 'TheTrainingMangerLMS\Quiz\Question');
		$response = array( 'answer' => array('3') );
		$result = $question->evaluate($response);
		$this->assertEquals(3, $result['score']);
		$this->assertEquals(TheTrainingMangerLMS\Quiz\SingleChoiceAnswer::NOT_APPLICABLE, $result['outcome']);
	}

	public function test_MatrixMatchingAnswer() {
		$choices = array();
		$parameters = array(
			'content' => 'Animals',
			'content_type' => 'text/plain',
			'points' => 4,
			'matches' => array( 'Cow', 'Rat', 'Moose' )
		);
		array_push($choices, new TheTrainingMangerLMS\Quiz\MatchingCriterion($parameters));
		$choice = new TheTrainingMangerLMS\Quiz\MatchingCriterion();
		$choice->content = 'Colors'; $choice->content_type = 'text/plain'; $choice->points = 5;
		$choice->matches = array( 'Red', 'Blue', 'Green', 'Yellow' );
		array_push($choices, $choice);
		$choice = new TheTrainingMangerLMS\Quiz\MatchingCriterion();
		$choice->content = ''; $choice->content_type = 'text/plain'; $choice->points = 3;
		$choice->matches = array( 'Carpet', 'TV' );
		array_push($choices, $choice);
		$matchingAnswer = new TheTrainingMangerLMS\Quiz\MatrixMatchingAnswer($choices);
		$this->assertEquals(12, $matchingAnswer->getTotalPossiblePoints());
		$question = TheTrainingMangerLMS\Quiz\Question::create(
			array( 'title' => 'This is an assessment question',
				   'text' => 'Match the following terms to the closest relevant topic.',
				   'answer' => $matchingAnswer
			) 
		);
		$response = array( 'answer' => array(
			hash(Answer::HASH_ALGO, 'Animals') => array('Moose', 'Cow', 'Rat'),
			hash(Answer::HASH_ALGO, 'Colors') => array('Green', 'Red', 'Blue', 'Yellow'),
			hash(Answer::HASH_ALGO, '') => array('Carpet', 'TV')
		) );
		$result = $question->evaluate($response);
		$this->assertEquals(12, $result['score']);
		$this->assertEquals(TheTrainingMangerLMS\Quiz\SingleChoiceAnswer::CORRECT, $result['outcome']);
		$response = array( 'answer' => array(
			hash(Answer::HASH_ALGO, 'Animals') => array('Moose', 'Red', 'Rat'),
			hash(Answer::HASH_ALGO, 'Colors') => array('Green', 'Cow', 'Blue', 'Yellow'),
			hash(Answer::HASH_ALGO, '') => array('Carpet', 'TV')
		) );
		$result = $question->evaluate($response);
		$this->assertEquals(3, $result['score']);
		$this->assertEquals(TheTrainingMangerLMS\Quiz\SingleChoiceAnswer::PARTIALLY_CORRECT, $result['outcome']);
	}

	public function test_clozeAnswer() {
		$cloze = new TheTrainingMangerLMS\Quiz\ClozeAnswer("The Capital of Indiana is {Indianapolis:2}.  A ball's shape is {round:2|spherical:4}");
		$this->assertEquals(6, $cloze->getTotalPossiblePoints());
		$question = TheTrainingMangerLMS\Quiz\Question::create(
			array( 'title' => 'This is a cloze question',
				   'text' => '',
				   'answer' => $cloze
			) 
		);
		$this->assertTrue(get_class($question) == 'TheTrainingMangerLMS\Quiz\Question');
		$response = array( 'answer' => array('indianapolis', 'spherical') );
		$result = $question->evaluate($response);
		$this->assertEquals(6, $result['score']);
		$this->assertEquals(TheTrainingMangerLMS\Quiz\SingleChoiceAnswer::CORRECT, $result['outcome']);
		$response = array( 'answer' => array('richmond', 'round') );
		$result = $question->evaluate($response);
		$this->assertEquals(2, $result['score']);
		$this->assertEquals(TheTrainingMangerLMS\Quiz\SingleChoiceAnswer::PARTIALLY_CORRECT, $result['outcome']);
	}
}

?>