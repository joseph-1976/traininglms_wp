<?php

class UtilityClassTest extends WP_UnitTestCase
{

	function test_getFeaturedCoursesList() {
        $course = TheTrainingMangerLMS\OnlineCourse::create( array( 'title' => 'This is my favorite course!') );
        $match = get_class($course) == 'TheTrainingMangerLMS\OnlineCourse';
        $this->assertTrue($match);
        if (!$match) $this->fail();
        $course2 = TheTrainingMangerLMS\OnlineCourse::create( array( 'title' => 'This is my least favorite course!') );
        $this->assertTrue(get_class($course2) == 'TheTrainingMangerLMS\OnlineCourse');
        $course2->setFeatured(true);
    	$course3 = TheTrainingMangerLMS\LiveCourse::create( array( 'title' => 'This is my favorite course!') );
    	$this->assertTrue(get_class($course3) == 'TheTrainingMangerLMS\LiveCourse');
    	$course3->setFeatured(true);
    	$courses = TheTrainingMangerLMS\Utility::getFeaturedCoursesList(array( 'type' => 'all'));
    	$this->assertEquals(2, count($courses));
    	$this->assertTrue(in_array($course2->ID(), $courses));
		$this->assertTrue(in_array($course3->ID(), $courses));
		$courses = TheTrainingMangerLMS\Utility::getFeaturedCoursesList(array( 'type' => 'online'));
		$this->assertEquals(1, count($courses));
		$this->assertTrue(in_array($course2->ID(), $courses));
		$courses = TheTrainingMangerLMS\Utility::getFeaturedCoursesList(array( 'type' => 'live'));
		$this->assertEquals(1, count($courses));
		$this->assertTrue(in_array($course3->ID(), $courses));
	}

	/**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid filter options for type; expecting one of 'all', 'online' or 'live'.
     */
	function test_getFeaturedCoursesListBadFilter() {
    	$courses = TheTrainingMangerLMS\Utility::getFeaturedCoursesList(array( 'type' => 'cow'));
    }

    function test_getAllOnlineCoursesList() {
        $course = TheTrainingMangerLMS\OnlineCourse::create( array( 'title' => 'This is my favorite course!') );
        $match = get_class($course) == 'TheTrainingMangerLMS\OnlineCourse';
        $this->assertTrue($match);
        if (!$match) $this->fail();
        $course2 = TheTrainingMangerLMS\OnlineCourse::create( array( 'title' => 'This is my least favorite course!') );
        $this->assertTrue(get_class($course2) == 'TheTrainingMangerLMS\OnlineCourse');
    	$course3 = TheTrainingMangerLMS\LiveCourse::create( array( 'title' => 'This is my favorite course!') );
    	$this->assertTrue(get_class($course3) == 'TheTrainingMangerLMS\LiveCourse');
    	$courses = TheTrainingMangerLMS\Utility::getAllOnlineCoursesList();
    	$this->assertEquals(2, count($courses));
    	$this->assertTrue(in_array($course->ID(), $courses));
    	$this->assertTrue(in_array($course2->ID(), $courses));
    }

    function test_getAllLiveCoursesList() {
        $course = TheTrainingMangerLMS\OnlineCourse::create( array( 'title' => 'This is my favorite course!') );
        $match = get_class($course) == 'TheTrainingMangerLMS\OnlineCourse';
        $this->assertTrue($match);
        if (!$match) $this->fail();
        $course2 = TheTrainingMangerLMS\LiveCourse::create( array( 'title' => 'This is my least favorite course!') );
        $this->assertTrue(get_class($course2) == 'TheTrainingMangerLMS\LiveCourse');
    	$course3 = TheTrainingMangerLMS\LiveCourse::create( array( 'title' => 'This is my favorite course!') );
    	$this->assertTrue(get_class($course3) == 'TheTrainingMangerLMS\LiveCourse');
    	$courses = TheTrainingMangerLMS\Utility::getAllLiveCoursesList();
    	$this->assertEquals(2, count($courses));
    	$this->assertTrue(in_array($course3->ID(), $courses));
    	$this->assertTrue(in_array($course2->ID(), $courses));
    }

}

?>