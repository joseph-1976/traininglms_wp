<?php

class LessonQuizClassTest extends WP_UnitTestCase
{

	public function test_createQuiz() {
    	$quiz = TheTrainingMangerLMS\LessonQuiz::create( array( 'title' => 'This is my favorite quiz!') );
    	$this->assertTrue(get_class($quiz) == 'TheTrainingMangerLMS\LessonQuiz');
        $this->assertEquals('This is my favorite quiz!', get_post_field('post_title', $quiz->ID(), 'db'));
        $this->assertEquals('TheTrainingMangerLMS\LessonQuiz', get_post_meta($quiz->ID(), ttp_lms_prefix('quiz_type'), true));
    }

	/**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Parameter options must be an array.
     */
    public function test_createQuizBadOptions() {
    	$quiz = TheTrainingMangerLMS\LessonQuiz::create(
    		array( 'title' => 'This is my favorite quiz!',
    			   'options' => 'bad'
    		)
    	);
    }

    public function test_createQuizUnknownOption() {
    	$quiz = TheTrainingMangerLMS\LessonQuiz::create(
    		array( 'title' => 'This is my favorite quiz!',
    			   'options' => array( 'unknown_option' => 'true' )
    		)
    	);
    }

    public function test_createQuizWithOptions() {
    	$quiz = TheTrainingMangerLMS\LessonQuiz::create(
    		array( 'title' => 'This is my favorite quiz!',
    			   'options' => array(
    			   		'number_of_questions' => 20,
    			   		'time_limit' => 3600,
    			   		'allowed_repeats' => 2
    			   	)
    		)
    	);
    	$this->assertTrue(get_class($quiz) == 'TheTrainingMangerLMS\LessonQuiz');
    	$this->assertEquals(20, $quiz->getOption('number_of_questions'));
    	$this->assertEquals(3600, $quiz->getOption('time_limit'));
    	$this->assertEquals(2, $quiz->getOption('allowed_repeats'));
    	$this->assertEquals(80, $quiz->getOption('pass_percentage'));
    }

    public function test_setQuizOption() {
     	$quiz = TheTrainingMangerLMS\LessonQuiz::create( array( 'title' => 'This is my favorite quiz!') );
    	$this->assertTrue(get_class($quiz) == 'TheTrainingMangerLMS\LessonQuiz');
    	$this->assertEquals(0, $quiz->getOption('number_of_questions'));
		$quiz->setOption('number_of_questions', 20);
		$this->assertEquals(20, $quiz->getOption('number_of_questions'));
		$this->assertEquals(0, $quiz->getOption('time_limit'));
		$quiz->setOption('time_limit', 3600);
		$this->assertEquals(3600, $quiz->getOption('time_limit'));
		$this->assertEquals(0, $quiz->getOption('allowed_repeats'));
		$quiz->setOption('allowed_repeats', 2);
		$this->assertEquals(2, $quiz->getOption('allowed_repeats'));
    }

    public function test_quizQuestionFunctions() {
     	$quiz = TheTrainingMangerLMS\LessonQuiz::create( array( 'title' => 'This is my favorite quiz!') );
    	$this->assertTrue(get_class($quiz) == 'TheTrainingMangerLMS\LessonQuiz');
		$question = TheTrainingMangerLMS\Quiz\Question::create( array( 'title' => 'This is my favorite question!') );
		$this->assertTrue(get_class($question) == 'TheTrainingMangerLMS\Quiz\Question');
		$questions = $quiz->getQuestions();
		$this->assertEquals(0, count($questions));
		$quiz->addQuestion($question);
		$questions = $quiz->getQuestions();
		$this->assertEquals(1, count($questions));
		$this->assertEquals($question->ID(), $questions[0]->ID());
		$question2 = TheTrainingMangerLMS\Quiz\Question::create( array( 'title' => 'This is my least favorite question!') );
		$this->assertTrue(get_class($question2) == 'TheTrainingMangerLMS\Quiz\Question');
		$quiz->addQuestion($question2);
		$questions = $quiz->getQuestions();
		$this->assertEquals(2, count($questions));
		$this->assertEquals($question->ID(), $questions[0]->ID());
		$this->assertEquals($question2->ID(), $questions[1]->ID());
		$quiz->removeQuestion($question);
		$questions = $quiz->getQuestions();
		$this->assertEquals(1, count($questions));
		$this->assertEquals($question2->ID(), $questions[0]->ID());
    }

    public function test_quizRandomQuestionsFunction() {
     	$quiz = TheTrainingMangerLMS\LessonQuiz::create( array( 'title' => 'This is my favorite quiz!') );
    	$this->assertTrue(get_class($quiz) == 'TheTrainingMangerLMS\LessonQuiz');
    	// create a whole bunch of questions, say 25 and add them
    	for($i = 0; $i < 25; $i++) {
			$question = TheTrainingMangerLMS\Quiz\Question::create( array( 'title' => "This is my favorite question {$i}!") );
			$quiz->addQuestion($question);
    	}
    	$questions = array_map(function($q) { return $q->ID(); }, $quiz->getQuestions());
    	$this->assertEquals(25, count($questions));
    	$random = $quiz->getRandomQuestionsList(20);
    	$this->assertEquals(20, count($random));
    	$diff = array_diff($questions, $random);
    	$this->assertEquals(5, count($diff));
    }

    public function createQuizAndUser() {
    	$quiz = TheTrainingMangerLMS\LessonQuiz::create(
    		array( 'title' => 'This is my favorite quiz!',
    			   'options' => array(
    			   		'number_of_questions' => 20,
    			   		'time_limit' => 3600,
    			   		'allowed_repeats' => 2
    			   	)
    		)
    	);
    	$this->assertTrue(get_class($quiz) == 'TheTrainingMangerLMS\LessonQuiz');
    	// create a whole bunch of questions, say 25 and add them
    	for($i = 0; $i < 25; $i++) {
			$question = TheTrainingMangerLMS\Quiz\Question::create( array( 'title' => "This is my favorite question {$i}!") );
			$quiz->addQuestion($question);
    	}
    	$this->assertEquals(25, count($quiz->getQuestions()));
    	$user = TheTrainingMangerLMS\User::create( array( 'login' => 'Joe Schmoe', 'password' => 'test123' ) );
    	$this->assertTrue(get_class($user) == 'TheTrainingMangerLMS\User');
    }

    public function test_quizStartQuizFunction() {
    	$quiz = TheTrainingMangerLMS\LessonQuiz::create(
    		array( 'title' => 'This is my favorite quiz!',
    			   'options' => array(
    			   		'number_of_questions' => 20,
    			   		'time_limit' => 3600,
    			   		'allowed_repeats' => 2
    			   	)
    		)
    	);
    	$this->assertTrue(get_class($quiz) == 'TheTrainingMangerLMS\LessonQuiz');
    	// create a whole bunch of questions, say 25 and add them
    	for($i = 0; $i < 25; $i++) {
			$question = TheTrainingMangerLMS\Quiz\Question::create( array( 'title' => "This is my favorite question {$i}!") );
			$quiz->addQuestion($question);
    	}
    	$this->assertEquals(25, count($quiz->getQuestions()));
    	$user = TheTrainingMangerLMS\User::create( array( 'login' => 'Joe Schmoe', 'password' => 'test123' ) );
    	$this->assertTrue(get_class($user) == 'TheTrainingMangerLMS\User');
        $user = TheTrainingMangerLMS\Student::promote($user);
        $this->assertTrue(get_class($user) == 'TheTrainingMangerLMS\Student');
    	$quiz->startQuiz($user);
    	// validate user_meta
    	$active = get_user_meta($user->ID(), ttp_lms_prefix('quiz_active'), false);
    	$this->assertTrue((count($active) == 1) && ($quiz->ID() == $active[0]));
    	$this->assertEquals($quiz->ID(), get_user_meta($user->ID(), ttp_lms_prefix('quiz_' . $quiz->ID()), true));
    	$this->assertEquals(0, get_user_meta($user->ID(), ttp_lms_prefix('quiz_' . $quiz->ID() . '_tries'), true));
    	$questions = get_user_meta($user->ID(), ttp_lms_prefix('quiz_' . $quiz->ID() . '_try_' . '1' . '_questions'), true);
    	$this->assertEquals(20, count(explode(',', $questions)));
    	$starttime = get_user_meta($user->ID(), ttp_lms_prefix('quiz_' . $quiz->ID() . '_starttime_utc'), false);
    	$this->assertEquals(1, count($starttime));
    }

	/**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Student is currently taking a quiz.
     */
    public function test_quizStartQuizTwice() {
    	$quiz = TheTrainingMangerLMS\LessonQuiz::create(
    		array( 'title' => 'This is my favorite quiz!',
    			   'options' => array(
    			   		'number_of_questions' => 20,
    			   		'time_limit' => 3600,
    			   		'allowed_repeats' => 2
    			   	)
    		)
    	);
    	$this->assertTrue(get_class($quiz) == 'TheTrainingMangerLMS\LessonQuiz');
    	// create a whole bunch of questions, say 25 and add them
    	for($i = 0; $i < 25; $i++) {
			$question = TheTrainingMangerLMS\Quiz\Question::create( array( 'title' => "This is my favorite question {$i}!") );
			$quiz->addQuestion($question);
    	}
    	$this->assertEquals(25, count($quiz->getQuestions()));
    	$user = TheTrainingMangerLMS\User::create( array( 'login' => 'Joe Schmoe', 'password' => 'test123' ) );
    	$this->assertTrue(get_class($user) == 'TheTrainingMangerLMS\User');
        $user = TheTrainingMangerLMS\Student::promote($user);
        $this->assertTrue(get_class($user) == 'TheTrainingMangerLMS\Student');
    	$quiz->startQuiz($user);
    	$quiz->startQuiz($user);
    }

    public function createSimpleChoiceAnswer() {
    	$choices = array();
    	$choice = new \TheTrainingMangerLMS\Quiz\SingleChoice();
    	$choice->content = "A"; $choice->content_type = 'text/plain'; $choice->points = 1; $choice->correct = true;
    	array_push($choices, $choice);
    	$choice = new \TheTrainingMangerLMS\Quiz\SingleChoice;
    	$choice->content = "B"; $choice->content_type = 'text/plain'; $choice->points = 1; $choice->correct = false;
    	array_push($choices, $choice);
    	$choice = new \TheTrainingMangerLMS\Quiz\SingleChoice;
    	$choice->content = "C"; $choice->content_type = 'text/plain'; $choice->points = 1; $choice->correct = false;
    	array_push($choices, $choice);
    	$choice = new \TheTrainingMangerLMS\Quiz\SingleChoice;
    	$choice->content = "D"; $choice->content_type = 'text/plain'; $choice->points = 1; $choice->correct = false;
    	array_push($choices, $choice);
    	return new \TheTrainingMangerLMS\Quiz\SingleChoiceAnswer($choices);
    }
    public function test_getNextQuestion() {
    	$quiz = TheTrainingMangerLMS\LessonQuiz::create(
    		array( 'title' => 'This is my favorite quiz!',
    			   'options' => array(
    			   		'number_of_questions' => 20,
    			   		'time_limit' => 3600,
    			   		'allowed_repeats' => 2
    			   	)
    		)
    	);
    	$this->assertTrue(get_class($quiz) == 'TheTrainingMangerLMS\LessonQuiz');
    	// create a whole bunch of questions, say 25 and add them
    	for($i = 0; $i < 25; $i++) {
			$question = TheTrainingMangerLMS\Quiz\Question::create( array( 'title' => "This is my favorite question {$i}!") );
			$question->setAnswer($this->createSimpleChoiceAnswer());
			$quiz->addQuestion($question);
    	}
    	$this->assertEquals(25, count($quiz->getQuestions()));
    	$user = TheTrainingMangerLMS\User::create( array( 'login' => 'Joe Schmoe', 'password' => 'test123' ) );
    	$this->assertTrue(get_class($user) == 'TheTrainingMangerLMS\User');
        $user = TheTrainingMangerLMS\Student::promote($user);
        $this->assertTrue(get_class($user) == 'TheTrainingMangerLMS\Student');
    	$quiz->startQuiz($user);
    	$questions = explode(',', get_user_meta($user->ID(), ttp_lms_prefix('quiz_' . $quiz->ID() . '_try_' . '1' . '_questions'), true));
    	for($i = 0; $i < 20; $i++) {
	    	$question = $quiz->getNextQuestion($user);
    		$this->assertEquals($questions[$i], $question->ID());
    		$quiz->saveQuestionResponseAndOutcome($user, $question, array('choice' => 1), array( 'outcome' => 'CORRECT', 'score' => 2.0, 'tpp' => 4.0));
    	}
    	$question = $quiz->getNextQuestion($user);
    	$this->assertTrue(is_null($question));
    }

    public function test_endQuizFunction() {
    	$quiz = TheTrainingMangerLMS\LessonQuiz::create(
    		array( 'title' => 'This is my favorite quiz!',
    			   'options' => array(
    			   		'number_of_questions' => 20,
    			   		'time_limit' => 3600,
    			   		'allowed_repeats' => 2
    			   	)
    		)
    	);
    	$this->assertTrue(get_class($quiz) == 'TheTrainingMangerLMS\LessonQuiz');
    	// create a whole bunch of questions, say 25 and add them
    	for($i = 0; $i < 25; $i++) {
			$question = TheTrainingMangerLMS\Quiz\Question::create( array( 'title' => "This is my favorite question {$i}!", 'answer' => $this->createSimpleChoiceAnswer() ) );
			//$question->setAnswer($this->createSimpleChoiceAnswer());
			$quiz->addQuestion($question);
    	}
    	$this->assertEquals(25, count($quiz->getQuestions()));
    	$user = TheTrainingMangerLMS\User::create( array( 'login' => 'Joe Schmoe', 'password' => 'test123' ) );
    	$this->assertTrue(get_class($user) == 'TheTrainingMangerLMS\User');
        $user = TheTrainingMangerLMS\Student::promote($user);
        $this->assertTrue(get_class($user) == 'TheTrainingMangerLMS\Student');
    	$quiz->startQuiz($user);
    	$questions = explode(',', get_user_meta($user->ID(), ttp_lms_prefix('quiz_' . $quiz->ID() . '_try_' . '1' . '_questions'), true));
    	for($i = 0; $i < 20; $i++) {
	    	$question = $quiz->getNextQuestion($user);
    		$this->assertEquals($questions[$i], $question->ID());
    		$quiz->saveQuestionResponseAndOutcome($user, $question, array('choice' => 1), array( 'outcome' => 'CORRECT', 'score' => 2.0, 'tpp' => 4.0));
    	}
    	$quiz->endQuiz($user);
    	// validate user_meta
    	$this->assertEquals(1, get_user_meta($user->ID(), ttp_lms_prefix('quiz_' . $quiz->ID() . '_tries'), true));
    	$active = get_user_meta($user->ID(), ttp_lms_prefix('quiz_active'), false);
    	$this->assertEquals(0, count($active));
    	$starttime = get_user_meta($user->ID(), ttp_lms_prefix('quiz_' . $quiz->ID() . '_try_' . '1' . '_starttime_utc'), false);
    	$this->assertEquals(1, count($starttime));
    	$totaltime = get_user_meta($user->ID(), ttp_lms_prefix('quiz_' . $quiz->ID() . '_try_' . '1' . '_totaltime'), false);
    	$this->assertEquals(1, count($totaltime));
    	$this->assertTrue(is_int($totaltime[0]+0));
    	$totalpoints = get_user_meta($user->ID(), ttp_lms_prefix('quiz_' . $quiz->ID() . '_try_' . '1' . '_ttp'), false);
    	$this->assertEquals(1, count($totalpoints));
    	$this->assertEquals(80, $totalpoints[0]);
    	$totalscore = get_user_meta($user->ID(), ttp_lms_prefix('quiz_' . $quiz->ID() . '_try_' . '1' . '_totalscore'), false);
    	$this->assertEquals(1, count($totalscore));
    	$this->assertEquals(40, $totalscore[0]);
    	// validate saved scores for each question
    	$total = 0;
    	foreach($questions as $question) {
    		$score = get_user_meta( $user->ID(), ttp_lms_prefix('quiz_' . $quiz->ID() . '_try_' . '1' . '_question_' . $question . '_score'), true );
    		$total += $score;
    	}
    	$this->assertEquals(40, $total);
    }

    public function test_quizTimeFunctions() {
    	$quiz = TheTrainingMangerLMS\LessonQuiz::create(
    		array( 'title' => 'This is my favorite quiz!',
    			   'options' => array(
    			   		'number_of_questions' => 20,
    			   		'time_limit' => 3600,
    			   		'allowed_repeats' => 2
    			   	)
    		)
    	);
    	$this->assertTrue(get_class($quiz) == 'TheTrainingMangerLMS\LessonQuiz');
    	// create a whole bunch of questions, say 25 and add them
    	for($i = 0; $i < 25; $i++) {
			$question = TheTrainingMangerLMS\Quiz\Question::create( array( 'title' => "This is my favorite question {$i}!") );
			$quiz->addQuestion($question);
    	}
    	$this->assertEquals(25, count($quiz->getQuestions()));
    	$user = TheTrainingMangerLMS\User::create( array( 'login' => 'Joe Schmoe', 'password' => 'test123' ) );
    	$this->assertTrue(get_class($user) == 'TheTrainingMangerLMS\User');
        $user = TheTrainingMangerLMS\Student::promote($user);
        $this->assertTrue(get_class($user) == 'TheTrainingMangerLMS\Student');
    	$quiz->startQuiz($user);
    	// fudge time
    	$datetime = new \DateTime(); $datetime->sub(new \DateInterval("PT2H")); $datetime->setTimeZone(new \DateTimeZone("UTC"));
		update_user_meta($user->ID(), ttp_lms_prefix('quiz_' . $quiz->ID() . '_starttime_utc'), $datetime->format("Y-m-d H:i:s"));
		$usertime = $quiz->getUserTime($user);
		$this->assertTrue($usertime >= 2* 3600);
		$this->assertTrue($quiz->hasUserTimeExpired($user));
    }

    public function test_quizUserScoreFunctions() {
    	// answer half the questions
    	$quiz = TheTrainingMangerLMS\LessonQuiz::create(
    		array( 'title' => 'This is my favorite quiz!',
    			   'options' => array(
    			   		'number_of_questions' => 20,
    			   		'time_limit' => 3600,
    			   		'allowed_repeats' => 2
    			   	)
    		)
    	);
    	$this->assertTrue(get_class($quiz) == 'TheTrainingMangerLMS\LessonQuiz');
    	// create a whole bunch of questions, say 25 and add them
    	for($i = 0; $i < 25; $i++) {
			$question = TheTrainingMangerLMS\Quiz\Question::create( array( 'title' => "This is my favorite question {$i}!") );
			$question->setAnswer($this->createSimpleChoiceAnswer());
			$quiz->addQuestion($question);
    	}
    	$this->assertEquals(25, count($quiz->getQuestions()));
    	$user = TheTrainingMangerLMS\User::create( array( 'login' => 'Joe Schmoe', 'password' => 'test123' ) );
    	$this->assertTrue(get_class($user) == 'TheTrainingMangerLMS\User');
        $user = TheTrainingMangerLMS\Student::promote($user);
        $this->assertTrue(get_class($user) == 'TheTrainingMangerLMS\Student');
    	$quiz->startQuiz($user);
    	$questions = explode(',', get_user_meta($user->ID(), ttp_lms_prefix('quiz_' . $quiz->ID() . '_try_' . '1' . '_questions'), true));
    	for($i = 0; $i < 10; $i++) {
	    	$question = $quiz->getNextQuestion($user);
    		$this->assertEquals($questions[$i], $question->ID());
    		$quiz->saveQuestionResponseAndOutcome($user, $question, array('choice' => 1), array( 'outcome' => 'CORRECT', 'score' => 2.0, 'tpp' => 4.0));
    	}
    	$quiz->endQuiz($user);
    	$totalpoints = get_user_meta($user->ID(), ttp_lms_prefix('quiz_' . $quiz->ID() . '_try_' . '1' . '_ttp'), false);
    	$this->assertEquals(1, count($totalpoints));
    	$this->assertEquals(80, $totalpoints[0]);
    	$totalscore = get_user_meta($user->ID(), ttp_lms_prefix('quiz_' . $quiz->ID() . '_try_' . '1' . '_totalscore'), false);
    	$this->assertEquals(1, count($totalscore));
    	$this->assertEquals(20, $totalscore[0]);
    }
}
