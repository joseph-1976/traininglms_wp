<?php

class CourseQuizClassTest extends WP_UnitTestCase
{

	public function test_createQuiz() {
    	$quiz = TheTrainingMangerLMS\CourseQuiz::create( array( 'title' => 'This is my favorite quiz!') );
    	$this->assertTrue(get_class($quiz) == 'TheTrainingMangerLMS\CourseQuiz');
        $this->assertEquals('This is my favorite quiz!', get_post_field('post_title', $quiz->ID(), 'db'));
        $this->assertEquals('TheTrainingMangerLMS\CourseQuiz', get_post_meta($quiz->ID(), ttp_lms_prefix('quiz_type'), true));
    }

    public function test_quizRandomQuestionsFunctionWithOutLesson() {
    	$quiz = TheTrainingMangerLMS\CourseQuiz::create(
    		array( 'title' => 'This is my favorite quiz!',
    			   'options' => array(
    			   		'number_of_questions' => 20,
    			   		'time_limit' => 3600,
    			   		'allowed_repeats' => 2
    			   	)
    		)
    	);
    	$this->assertTrue(get_class($quiz) == 'TheTrainingMangerLMS\CourseQuiz');
    	// create a whole bunch of questions, say 25 and add them
    	for($i = 0; $i < 25; $i++) {
			$question = TheTrainingMangerLMS\Quiz\Question::create( array( 'title' => "This is my favorite question {$i}!") );
			$quiz->addQuestion($question);
    	}
    	$questions = array_map(function($q) { return $q->ID(); }, $quiz->getQuestions());
    	$this->assertEquals(25, count($questions));
    	$course = TheTrainingMangerLMS\OnlineCourse::create( array( 'title' => 'This is my favorite course!') );
    	$this->assertTrue(get_class($course) == 'TheTrainingMangerLMS\OnlineCourse');
    	$course->setQuiz($quiz);
    	$random = $quiz->getRandomQuestionsList(array('course_number' => 20, 'lesson_number' => 20));
    	$this->assertEquals(20, count($random));
    	$diff = array_diff($questions, $random);
    	$this->assertEquals(5, count($diff));
    }

    public function test_quizRandomQuestionsFunctionWithLessonWithNoQuiz() {
    	$quiz = TheTrainingMangerLMS\CourseQuiz::create(
    		array( 'title' => 'This is my favorite quiz!',
    			   'options' => array(
    			   		'number_of_questions' => 20,
    			   		'time_limit' => 3600,
    			   		'allowed_repeats' => 2
    			   	)
    		)
    	);
    	$this->assertTrue(get_class($quiz) == 'TheTrainingMangerLMS\CourseQuiz');
    	// create a whole bunch of questions, say 25 and add them
    	for($i = 0; $i < 25; $i++) {
			$question = TheTrainingMangerLMS\Quiz\Question::create( array( 'title' => "This is my favorite question {$i}!") );
			$quiz->addQuestion($question);
    	}
    	$questions = array_map(function($q) { return $q->ID(); }, $quiz->getQuestions());
    	$this->assertEquals(25, count($questions));
    	$course = TheTrainingMangerLMS\OnlineCourse::create( array( 'title' => 'This is my favorite course!') );
    	$this->assertTrue(get_class($course) == 'TheTrainingMangerLMS\OnlineCourse');
        $lesson = TheTrainingMangerLMS\OnlineLesson::create( array( 'title' => 'This is my favorite lesson!') );
        $this->assertTrue(get_class($lesson) == 'TheTrainingMangerLMS\OnlineLesson');
        $course->addLesson($lesson);
    	$course->setQuiz($quiz);
    	$random = $quiz->getRandomQuestionsList(array('course_number' => 20, 'lesson_number' => 20));
    	$this->assertEquals(20, count($random));
    	$diff = array_diff($questions, $random);
    	$this->assertEquals(5, count($diff));
    }

    public function test_quizRandomQuestionsFunctionWithLessonWithQuiz() {
    	$quiz = TheTrainingMangerLMS\CourseQuiz::create(
    		array( 'title' => 'This is my favorite quiz!',
    			   'options' => array(
    			   		'number_of_questions' => 20,
    			   		'number_of_lesson_questions' => 10,
    			   		'time_limit' => 3600,
    			   		'allowed_repeats' => 2
    			   	)
    		)
    	);
    	$this->assertTrue(get_class($quiz) == 'TheTrainingMangerLMS\CourseQuiz');
    	// create a whole bunch of questions, say 25 and add them
    	for($i = 0; $i < 25; $i++) {
			$question = TheTrainingMangerLMS\Quiz\Question::create( array( 'title' => "This is my favorite question {$i}!") );
			$quiz->addQuestion($question);
    	}
    	$questions = array_map(function($q) { return $q->ID(); }, $quiz->getQuestions());
    	$this->assertEquals(25, count($questions));
    	$quizl = TheTrainingMangerLMS\LessonQuiz::create(
    		array( 'title' => 'This is my favorite quiz!',
    			   'options' => array(
    			   		'number_of_questions' => 20,
    			   		'time_limit' => 3600,
    			   		'allowed_repeats' => 2
    			   	)
    		)
    	);
    	$this->assertTrue(get_class($quizl) == 'TheTrainingMangerLMS\LessonQuiz');
    	// create a whole bunch of questions, say 25 and add them
    	for($i = 0; $i < 25; $i++) {
			$question = TheTrainingMangerLMS\Quiz\Question::create( array( 'title' => "This is my favorite question {$i}!") );
			$quizl->addQuestion($question);
    	}
    	$questionsl = array_map(function($q) { return $q->ID(); }, $quizl->getQuestions());
    	$this->assertEquals(25, count($questionsl));
    	$course = TheTrainingMangerLMS\OnlineCourse::create( array( 'title' => 'This is my favorite course!') );
    	$this->assertTrue(get_class($course) == 'TheTrainingMangerLMS\OnlineCourse');
        $lesson = TheTrainingMangerLMS\OnlineLesson::create( array( 'title' => 'This is my favorite lesson!') );
        $this->assertTrue(get_class($lesson) == 'TheTrainingMangerLMS\OnlineLesson');
        $course->addLesson($lesson);
    	$course->setQuiz($quiz);
    	$lesson->setQuiz($quizl);
    	$random = $quiz->getRandomQuestionsList(array('course_number' => 20, 'lesson_number' => 10));
    	$this->assertEquals(30, count($random));
    	$diff = array_diff($questions, $random);
    	$this->assertEquals(5, count($diff));
    	$diff = array_diff($questionsl, $random);
    	$this->assertEquals(15, count($diff));
    }

    public function test_startQuiz() {
    	$quiz = TheTrainingMangerLMS\CourseQuiz::create(
    		array( 'title' => 'This is my favorite quiz!',
    			   'options' => array(
    			   		'number_of_questions' => 10,
    			   		'time_limit' => 3600,
    			   		'allowed_repeats' => 2,
	   			   		'number_of_lesson_questions' => 10,
    			   	),
    		)
    	);
    	$this->assertTrue(get_class($quiz) == 'TheTrainingMangerLMS\CourseQuiz');
    	// create a whole bunch of questions, say 25 and add them
    	for($i = 0; $i < 25; $i++) {
			$question = TheTrainingMangerLMS\Quiz\Question::create( array( 'title' => "This is my favorite question {$i}!") );
			$quiz->addQuestion($question);
    	}
    	$questions = array_map(function($q) { return $q->ID(); }, $quiz->getQuestions());
    	$this->assertEquals(25, count($questions));
    	$quizl = TheTrainingMangerLMS\LessonQuiz::create(
    		array( 'title' => 'This is my favorite quiz!',
    			   'options' => array(
    			   		'number_of_questions' => 20,
    			   		'time_limit' => 3600,
    			   		'allowed_repeats' => 2
    			   	)
    		)
    	);
    	$this->assertTrue(get_class($quizl) == 'TheTrainingMangerLMS\LessonQuiz');
    	// create a whole bunch of questions, say 25 and add them
    	for($i = 0; $i < 25; $i++) {
			$question = TheTrainingMangerLMS\Quiz\Question::create( array( 'title' => "This is my favorite question {$i}!") );
			$quizl->addQuestion($question);
    	}
    	$questionsl = array_map(function($q) { return $q->ID(); }, $quizl->getQuestions());
    	$this->assertEquals(25, count($questionsl));
    	$course = TheTrainingMangerLMS\OnlineCourse::create( array( 'title' => 'This is my favorite course!') );
    	$this->assertTrue(get_class($course) == 'TheTrainingMangerLMS\OnlineCourse');
        $lesson = TheTrainingMangerLMS\OnlineLesson::create( array( 'title' => 'This is my favorite lesson!') );
        $this->assertTrue(get_class($lesson) == 'TheTrainingMangerLMS\OnlineLesson');
        $course->addLesson($lesson);
    	$course->setQuiz($quiz);
    	$lesson->setQuiz($quizl);
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

}

?>