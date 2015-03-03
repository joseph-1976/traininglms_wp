<?php

class OnlineLessonClassTest extends WP_UnitTestCase
{

    function test_createLesson() {
    	$lesson = TheTrainingMangerLMS\OnlineLesson::create( array( 'title' => 'This is my favorite lesson!') );
    	$this->assertTrue(get_class($lesson) == 'TheTrainingMangerLMS\OnlineLesson');
        $this->assertEquals('This is my favorite lesson!', get_post_field('post_title', $lesson->ID(), 'db'));
        $this->assertEquals('TheTrainingMangerLMS\OnlineLesson', get_post_meta($lesson->ID(), ttp_lms_prefix('lesson_type'), true));
    }

	/**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Unknown field name(s) foo in parameter list.
     */
    function test_createLessonWithBadParameters() {
    	$course = TheTrainingMangerLMS\OnlineLesson::create( array( 'foo' => 'This is my favorite lesson!') );
    }

/*    function test_createLessonWithManyParameters() {
        $course = TheTrainingMangerLMS\OnlineCourse::create(
            array( 
                'title' => 'This is my favorite course!',
            )
        );
        // Make sure it's created
        $this->assertTrue(get_class($course) == 'TheTrainingMangerLMS\OnlineCourse');
        // verify field values exist manually
        $this->assertEquals(get_post_meta($course->ID(), ttp_lms_prefix('course_access_control_enabled'), true), 'false');
        $this->assertEquals(get_post_meta($course->ID(), ttp_lms_prefix('course_default_access_period'), true), '15');
        $this->assertEquals(get_post_meta($course->ID(), ttp_lms_prefix('course_level_of_difficulty'), true), 'beginner');
        $this->assertEquals(get_post_field('post_title', $course->ID(), 'db'), 'This is my favorite course!');
    }*/

    function test_addTopic() {
        $lesson = TheTrainingMangerLMS\OnlineLesson::create( array( 'title' => 'This is my favorite lesson!') );
        $match = get_class($lesson) == 'TheTrainingMangerLMS\OnlineLesson';
        $this->assertTrue($match);
        if (!$match) $this->fail();
        $topic = TheTrainingMangerLMS\Topic::create( array( 'title' => 'This is my favorite topic!') );
        $match = get_class($topic) == 'TheTrainingMangerLMS\Topic';
        $this->assertTrue($match);
        if (!$match) $this->fail();
        $this->assertEquals(0, count($lesson->getTopics()));
        $lesson->addTopic($topic);
        $topics = $lesson->getTopics();
        $this->assertEquals(1, count($topics));
        $this->assertEquals($topics[0]->ID(), $topic->ID());
        // verify added manually
        $temp = get_post_meta($lesson->ID(), ttp_lms_prefix('lesson_topics'), true);
        $this->assertTrue( in_array($topic->ID(), $temp) );

        $topic2 =  TheTrainingMangerLMS\Topic::create( array( 'title' => 'This is my least favorite topic!') );
        $lesson->addTopic($topic2);
        $topics = $lesson->getTopics();
        $this->assertEquals(2, count($topics));
        $this->assertEquals($topics[0]->ID(), $topic->ID());
        $this->assertEquals($topics[1]->ID(), $topic2->ID());
        // verify added manually
        $temp = get_post_meta($lesson->ID(), ttp_lms_prefix('lesson_topics'), true);
        $this->assertTrue( in_array($topic2->ID(), $temp) );
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Topic already added.
     */
    function test_addSameTopic() {
        $lesson = TheTrainingMangerLMS\OnlineLesson::create( array( 'title' => 'This is my favorite lesson!') );
        $match = get_class($lesson) == 'TheTrainingMangerLMS\OnlineLesson';
        $this->assertTrue($match);
        if (!$match) $this->fail();
        $topic = TheTrainingMangerLMS\Topic::create( array( 'title' => 'This is my favorite topic!') );
        $match = get_class($topic) == 'TheTrainingMangerLMS\Topic';
        $this->assertTrue($match);
        if (!$match) $this->fail();
        $lesson->addTopic($topic);
        // verify added
        $topics = $lesson->getTopics();
        $this->assertEquals(1, count($topics));
        $this->assertEquals($topic->ID(), $topics[0]->ID());
        // try to add it again
        $lesson->addTopic($topic);
    }

    function test_insertTopic() {
        $lesson = TheTrainingMangerLMS\OnlineLesson::create( array( 'title' => 'This is my favorite lesson!') );
        $match = get_class($lesson) == 'TheTrainingMangerLMS\OnlineLesson';
        $this->assertTrue($match);
        if (!$match) $this->fail();
        $topic = TheTrainingMangerLMS\Topic::create( array( 'title' => 'This is my favorite topic!') );
        $this->assertEquals(get_class($topic), 'TheTrainingMangerLMS\Topic');
        $topic2 = TheTrainingMangerLMS\Topic::create( array( 'title' => 'This is my least favorite topic!') );
        $this->assertEquals(get_class($topic2), 'TheTrainingMangerLMS\Topic');
        $lesson->addTopic($topic);
        $lesson->addTopic($topic2);
        $topics = $lesson->getTopics();
        $this->assertEquals(2, count($topics));
        $topic3 = TheTrainingMangerLMS\Topic::create( array( 'title' => 'This is topic is mediocre!') );
        $this->assertEquals(get_class($topic3), 'TheTrainingMangerLMS\Topic');
        $lesson->insertTopic(1, $topic3);
        $topics = $lesson->getTopics();
        $this->assertEquals(3, count($topics));
        $this->assertEquals($topic->ID(), $topics[0]->ID());
        $this->assertEquals($topic3->ID(), $topics[1]->ID());
        $this->assertEquals($topic2->ID(), $topics[2]->ID());
    }

    /**
     * @expectedException OutOfBoundsException
     * @expectedExceptionMessage Index out of bounds.
     */
    function test_insertTopicBadIndex() {
        $lesson = TheTrainingMangerLMS\OnlineLesson::create( array( 'title' => 'This is my favorite lesson!') );
        $match = get_class($lesson) == 'TheTrainingMangerLMS\OnlineLesson';
        $this->assertTrue($match);
        if (!$match) $this->fail();
        $topic = TheTrainingMangerLMS\Topic::create( array( 'title' => 'This is my favorite topic!') );
        $this->assertEquals(get_class($topic), 'TheTrainingMangerLMS\Topic');
        $lesson->insertTopic(1, $topic);
    }

    function test_removeTopic() {
        $lesson = TheTrainingMangerLMS\OnlineLesson::create( array( 'title' => 'This is my favorite lesson!') );
        $match = get_class($lesson) == 'TheTrainingMangerLMS\OnlineLesson';
        $this->assertTrue($match);
        if (!$match) $this->fail();
        $topic = TheTrainingMangerLMS\Topic::create( array( 'title' => 'This is my favorite topic!') );
        $this->assertEquals(get_class($topic), 'TheTrainingMangerLMS\Topic');
        $topic2 = TheTrainingMangerLMS\Topic::create( array( 'title' => 'This is my least favorite topic!') );
        $this->assertEquals(get_class($topic2), 'TheTrainingMangerLMS\Topic');
        $lesson->addTopic($topic);
        $lesson->addTopic($topic2);
        $topics = $lesson->getTopics();
        $this->assertEquals(2, count($topics));
        $lesson->removeTopic($topic);
        $topics = $lesson->getTopics();
        $this->assertEquals(1, count($topics));
        $this->assertEquals($topic2->ID(), $topics[0]->ID());
        $lesson->removeTopic($topic2);
        $topics = $lesson->getTopics();
        $this->assertEquals(0, count($topics));
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Topic does not belong to this lesson.
     */
    function test_removeTopicBadTopic() {
        $lesson = TheTrainingMangerLMS\OnlineLesson::create( array( 'title' => 'This is my favorite lesson!') );
        $match = get_class($lesson) == 'TheTrainingMangerLMS\OnlineLesson';
        $this->assertTrue($match);
        if (!$match) $this->fail();
        $topic = TheTrainingMangerLMS\Topic::create( array( 'title' => 'This is my favorite topic!') );
        $this->assertEquals(get_class($topic), 'TheTrainingMangerLMS\Topic');
        $lesson->removeTopic($topic);
    }

    function test_estimatedTime() {
        $lesson = TheTrainingMangerLMS\OnlineLesson::create( array( 'title' => 'This is my favorite lesson!', 'estimated_time' => 3600 ) );
        $match = get_class($lesson) == 'TheTrainingMangerLMS\OnlineLesson';
        $this->assertEquals(3600, $lesson->getEstimatedTime());
        $lesson->setEstimatedTime(3600 * 2);
        $this->assertEquals(7200, $lesson->getEstimatedTime());
        // verify manually
        $time = get_post_meta($lesson->ID(), ttp_lms_prefix('lesson_estimated_time'), true);
        $this->assertEquals(7200, $time);
    }

}
