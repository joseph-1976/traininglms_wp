<?php

class OnlineCourseClassTest extends WP_UnitTestCase //PHPUnit_Framework_TestCase
{
private $createdCourses = array();
private $createdLessons = array();

    public static function setUpBeforeClass()
    {
    	parent::setUpBeforeClass();
        // do sth before the first test
    } 

    public static function tearDownAfterClass()
    {
        // do sth after the last test
        parent::tearDownAfterClass();
    }

    function test_createCourse() {
    	$course = TheTrainingMangerLMS\OnlineCourse::create( array( 'title' => 'This is my favorite course!') );
    	$this->assertTrue(get_class($course) == 'TheTrainingMangerLMS\OnlineCourse');
        $this->assertEquals('This is my favorite course!', get_post_field('post_title', $course->ID(), 'db'));
        $this->assertEquals('TheTrainingMangerLMS\OnlineCourse', get_post_meta($course->ID(), ttp_lms_prefix('course_type'), true));
    }

	/**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Unknown field name(s) foo in parameter list.
     */
    function test_createCourseWithBadParameters() {
    	$course = TheTrainingMangerLMS\OnlineCourse::create( array( 'foo' => 'This is my favorite course!') );
    }

    function test_createCourseWithManyParameters() {
        $course = TheTrainingMangerLMS\OnlineCourse::create(
            array( 
                'title' => 'This is my favorite course!',
                'access_control_enabled' => 'false',
                'default_access_period' => 15,
                'level_of_difficulty' => 'beginner'
            )
        );
        // Make sure it's created
        $this->assertTrue(get_class($course) == 'TheTrainingMangerLMS\OnlineCourse');
        // verify field values exist manually
        $this->assertEquals(get_post_meta($course->ID(), ttp_lms_prefix('course_access_control_enabled'), true), 'false');
        $this->assertEquals(get_post_meta($course->ID(), ttp_lms_prefix('course_default_access_period'), true), '15');
        $this->assertEquals(get_post_meta($course->ID(), ttp_lms_prefix('course_level_of_difficulty'), true), 'beginner');
        $this->assertEquals(get_post_field('post_title', $course->ID(), 'db'), 'This is my favorite course!');
    }

    function test_addLesson() {
        $course = TheTrainingMangerLMS\OnlineCourse::create( array( 'title' => 'This is my favorite course!') );
        $match = get_class($course) == 'TheTrainingMangerLMS\OnlineCourse';
        $this->assertTrue($match);
        if (!$match) $this->fail();
        $lesson = TheTrainingMangerLMS\OnlineLesson::create( array( 'title' => 'This is my favorite lesson!') );
        $match = get_class($lesson) == 'TheTrainingMangerLMS\OnlineLesson';
        $this->assertTrue($match);
        if (!$match) $this->fail();
        $this->assertEquals(0, count($course->getLessons()));
        // add lesson to course
        $course->addLesson($lesson);
        // verify added
        $lessons = $course->getLessons();
        $this->assertEquals(1, count($lessons));
        $this->assertEquals($lesson->ID(), $lessons[0]->ID());
        // verify added manually
        $temp = get_post_meta($course->ID(), ttp_lms_prefix('course_lessons'), true);
        $this->assertTrue( in_array($lesson->ID(), $temp) );

        $lesson2 = TheTrainingMangerLMS\OnlineLesson::create( array( 'title' => 'This is my least favorite lesson!') );
        $match = get_class($lesson2) == 'TheTrainingMangerLMS\OnlineLesson';
        $this->assertTrue($match);
        if (!$match) $this->fail();
        // add this lesson
        $course->addLesson($lesson2);
        // verify added
        $lessons = $course->getLessons();
        $this->assertEquals(2, count($lessons));
        $this->assertEquals($lesson2->ID(), $lessons[1]->ID());
        // verify added manually
        $temp = get_post_meta($course->ID(), ttp_lms_prefix('course_lessons'), true);
        $this->assertTrue( in_array($lesson2->ID(), $temp) );
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Lesson already added.
     */
    function test_addLessonAgain() {
        $course = TheTrainingMangerLMS\OnlineCourse::create( array( 'title' => 'This is my favorite course!') );
        $match = get_class($course) == 'TheTrainingMangerLMS\OnlineCourse';
        $this->assertTrue($match);
        if (!$match) $this->fail();
        $lesson = TheTrainingMangerLMS\OnlineLesson::create( array( 'title' => 'This is my favorite lesson!') );
        $match = get_class($lesson) == 'TheTrainingMangerLMS\OnlineLesson';
        $this->assertTrue($match);
        if (!$match) $this->fail();
        $course->addLesson($lesson);
        // verify added
        $lessons = $course->getLessons();
        $this->assertEquals(1, count($lessons));
        $this->assertEquals($lesson->ID(), $lessons[0]->ID());
        // try to add it again
        $course->addLesson($lesson);
    }

    function test_insertlesson() {
        $course = TheTrainingMangerLMS\OnlineCourse::create( array( 'title' => 'This is my favorite course!') );
        $match = get_class($course) == 'TheTrainingMangerLMS\OnlineCourse';
        $this->assertTrue($match);
        if (!$match) $this->fail();
        $lessons = array();
        for($i = 0; $i < 5; $i++) {
            $lesson = TheTrainingMangerLMS\OnlineLesson::create( array( 'title' => 'This is my favorite lesson {$i}!') );
            array_push($lessons, $lesson);
        }
        $this->assertEquals(5, count($lessons));
        $course->addLesson($lessons[0]); $course->addLesson($lessons[1]); $course->addLesson($lessons[2]);
        $course->insertLesson(0, $lessons[3]);
        $courseLessons = $course->getLessons();
        $this->assertEquals($lessons[3]->ID(), $courseLessons[0]->ID());
        $this->assertEquals($lessons[0]->ID(), $courseLessons[1]->ID());
        $this->assertEquals($lessons[1]->ID(), $courseLessons[2]->ID());
        $this->assertEquals($lessons[2]->ID(), $courseLessons[3]->ID());
        $course->insertLesson(2, $lessons[4]);
        $courseLessons = $course->getLessons();
        $this->assertEquals($lessons[3]->ID(), $courseLessons[0]->ID());
        $this->assertEquals($lessons[0]->ID(), $courseLessons[1]->ID());
        $this->assertEquals($lessons[4]->ID(), $courseLessons[2]->ID());
        $this->assertEquals($lessons[1]->ID(), $courseLessons[3]->ID());
        $this->assertEquals($lessons[2]->ID(), $courseLessons[4]->ID());
        // insert at end automatically tested with addLesson()
    }

    /**
     * @expectedException OutOfBoundsException
     * @expectedExceptionMessage Index out of bounds.
     */
    function test_insertLessonBadIndex() {
        $course = TheTrainingMangerLMS\OnlineCourse::create( array( 'title' => 'This is my favorite course!') );
        $match = get_class($course) == 'TheTrainingMangerLMS\OnlineCourse';
        $this->assertTrue($match);
        if (!$match) $this->fail();
        $lesson = TheTrainingMangerLMS\OnlineLesson::create( array( 'title' => 'This is my favorite lesson!') );
        $match = get_class($lesson) == 'TheTrainingMangerLMS\OnlineLesson';
        $this->assertTrue($match);
        if (!$match) $this->fail();
        $course->insertLesson(2, $lesson);
    }

    function test_removeLesson() {
        $course = TheTrainingMangerLMS\OnlineCourse::create( array( 'title' => 'This is my favorite course!') );
        $match = get_class($course) == 'TheTrainingMangerLMS\OnlineCourse';
        $this->assertTrue($match);
        if (!$match) $this->fail();
        $lessons = array();
        for($i = 0; $i < 3; $i++) {
            $lesson = TheTrainingMangerLMS\OnlineLesson::create( array( 'title' => 'This is my favorite lesson {$i}!') );
            array_push($lessons, $lesson);
            $course->addLesson($lesson);
        }
        $course->removeLesson($lessons[1]);
        $courseLessons = $course->getLessons();
        $this->assertEquals(2, count($courseLessons));
        $this->assertEquals($lessons[0]->ID(), $courseLessons[0]->ID());
        $this->assertEquals($lessons[2]->ID(), $courseLessons[1]->ID());
    }

    function test_addPrerequisite() {
        $course = TheTrainingMangerLMS\OnlineCourse::create( array( 'title' => 'This is my favorite course!') );
        $match = get_class($course) == 'TheTrainingMangerLMS\OnlineCourse';
        $this->assertTrue($match);
        if (!$match) $this->fail();
        $prereq = TheTrainingMangerLMS\OnlineCourse::create( array( 'title' => 'This is my least favorite course!') );
        $match = get_class($prereq) == 'TheTrainingMangerLMS\OnlineCourse';
        $this->assertTrue($match);
        if (!$match) $this->fail();
        $course->addPrerequisite($prereq);
        $prereqs = $course->getPrerequisites();
        $this->assertEquals(1, count($prereqs));
        $this->assertEquals($prereq->ID(), $prereqs[0]->ID());
        $prereq2 = TheTrainingMangerLMS\OnlineCourse::create( array( 'title' => 'This course is okay!') );
        $course->addPrerequisite($prereq2);
        $prereqs = $course->getPrerequisites();
        $this->assertEquals(2, count($prereqs));
        $this->assertEquals($prereq->ID(), $prereqs[0]->ID());
        $this->assertEquals($prereq2->ID(), $prereqs[1]->ID());
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Can't add course as its own prerequisite.
     */
    function test_addPrerequisiteItself() {
        $course = TheTrainingMangerLMS\OnlineCourse::create( array( 'title' => 'This is my favorite course!') );
        $match = get_class($course) == 'TheTrainingMangerLMS\OnlineCourse';
        $this->assertTrue($match);
        if (!$match) $this->fail();
        $course->addPrerequisite($course);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Course is already a prerequisite.
     */
    function test_addPrerequisiteSameCourse() {
        $course = TheTrainingMangerLMS\OnlineCourse::create( array( 'title' => 'This is my favorite course!') );
        $match = get_class($course) == 'TheTrainingMangerLMS\OnlineCourse';
        $this->assertTrue($match);
        if (!$match) $this->fail();
        $prereq = TheTrainingMangerLMS\OnlineCourse::create( array( 'title' => 'This is my least favorite course!') );
        $course->addPrerequisite($prereq);
        $course->addPrerequisite($prereq);
    }

    function test_removePrerequisite() {
        $course = TheTrainingMangerLMS\OnlineCourse::create( array( 'title' => 'This is my favorite course!') );
        $match = get_class($course) == 'TheTrainingMangerLMS\OnlineCourse';
        $this->assertTrue($match);
        if (!$match) $this->fail();
        $prereq = TheTrainingMangerLMS\OnlineCourse::create( array( 'title' => 'This is my least favorite course!') );
        $prereq2 = TheTrainingMangerLMS\OnlineCourse::create( array( 'title' => 'This course is okay!') );
        $course->addPrerequisite($prereq);
        $course->addPrerequisite($prereq2);
        $prereqs = $course->getPrerequisites();
        $this->assertEquals(2, count($prereqs));
        $course->removePrerequisite($prereq);
        $prereqs = $course->getPrerequisites();
        $this->assertEquals(1, count($prereqs));
        $this->assertEquals($prereq2->ID(), $prereqs[0]->ID());
        $course->removePrerequisite($prereq2);
        $prereqs = $course->getPrerequisites();
        $this->assertEquals(0, count($prereqs));
    }

     /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Course is not a prerequisite.
     */
   function test_removeNonExistantPrerequisite() {
        $course = TheTrainingMangerLMS\OnlineCourse::create( array( 'title' => 'This is my favorite course!') );
        $match = get_class($course) == 'TheTrainingMangerLMS\OnlineCourse';
        $this->assertTrue($match);
        if (!$match) $this->fail();
        $course2 = TheTrainingMangerLMS\OnlineCourse::create( array( 'title' => 'This is my least favorite course!') );
        $course->removePrerequisite($course2);
    }

    function test_accessFunctionality() {
        $this->markTestIncomplete();
    }
    function test_isAccessControlEnabled() {
        $course = TheTrainingMangerLMS\OnlineCourse::create( array( 'title' => 'This is my favorite course!') );
        $this->assertEquals(true, $course->isAccessControlEnabled());
        $course->setAccessControlEnabled(false);
        $this->assertEquals(false, $course->isAccessControlEnabled());
    }
    function test_setAccessControl() {
        $course = TheTrainingMangerLMS\OnlineCourse::create( array( 'title' => 'This is my favorite course!') );
        $this->assertEquals(true, $course->isAccessControlEnabled());
        $course->setAccessControlEnabled(false);
        $this->assertEquals(false, $course->isAccessControlEnabled());
        $temp = get_post_meta($course->ID(), ttp_lms_prefix('course_access_control_enabled'), true);
        $this->assertEquals('false', $temp);
    }

    function test_userAccessFunctionality() {
        $this->markTestIncomplete();
    }

    function test_update() {
        $course = TheTrainingMangerLMS\OnlineCourse::create( array( 'title' => 'This is my favorite course!') );
        $this->assertEquals($course->getTitle(), 'This is my favorite course!');
        $course->update(array( 'title' => 'Updated title' ));
        $this->assertEquals($course->getTitle(), 'Updated title');
    }

    function test_productCreation() {
        $course = TheTrainingMangerLMS\OnlineCourse::create( array( 'title' => 'This is my favorite course!') );
        $product = \TheTrainingMangerLMS\Utility::getAssociatedProduct('_course_id', $course->ID());
        $this->assertFalse(is_null($product));
    }

    function test_productTitleUpdate() {
        $course = TheTrainingMangerLMS\OnlineCourse::create( array( 'title' => 'This is my favorite course!') );
        $this->assertEquals($course->getTitle(), 'This is my favorite course!');
        $product = \TheTrainingMangerLMS\Utility::getAssociatedProduct('_course_id', $course->ID());
        $this->assertEquals($course->getTitle(), $product->getTitle());
        $course->update(array( 'title' => 'Updated title' ));
        $this->assertEquals($course->getTitle(), 'Updated title');
        $product = \TheTrainingMangerLMS\Utility::getAssociatedProduct('_course_id', $course->ID());
        $this->assertFalse(is_null($product));
        $this->assertEquals($course->getTitle(), $product->getTitle());
    }

    function test_LessonTrainerAssociation() {
        $course = TheTrainingMangerLMS\OnlineCourse::create( array( 'title' => 'This is my favorite course!') );
        $match = get_class($course) == 'TheTrainingMangerLMS\OnlineCourse';
        $this->assertTrue($match);
        if (!$match) $this->fail();
        $lesson = TheTrainingMangerLMS\OnlineLesson::create( array( 'title' => 'This is my favorite lesson!') );
        $match = get_class($lesson) == 'TheTrainingMangerLMS\OnlineLesson';
        $this->assertTrue($match);
        if (!$match) $this->fail();
        $this->assertEquals(0, count($course->getLessons()));
        // add lesson to course
        $course->addLesson($lesson);
        // verify added
        $lessons = $course->getLessons();
        $this->assertEquals(1, count($lessons));
        $this->assertEquals($lesson->ID(), $lessons[0]->ID());

        // create user
        $trainer = TheTrainingMangerLMS\Trainer::create( array( 'login' => 'Joe Schmoe', 'password' => 'test123' ) );
        $this->assertTrue(get_class($trainer) == 'TheTrainingMangerLMS\Trainer');

        $this->assertTrue(is_null($course->getLessonTrainer($lesson)));
        $course->setLessonTrainer($lesson, $trainer);
        $lesson_trainer = $course->getLessonTrainer($lesson);
        $this->assertEquals($trainer->ID(), $lesson_trainer->ID());
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Missing values for required field(s) title.
     */
    function test_createCourseWithoutTitle() {
        $course = TheTrainingMangerLMS\OnlineCourse::create( array( ) );
    }

}

?>
