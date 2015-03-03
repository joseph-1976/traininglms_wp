<?php

class OnlineUserClassTest extends WP_UnitTestCase
{

    function test_createUser() {
    	$user = TheTrainingMangerLMS\User::create( array( 'login' => 'Joe Schmoe', 'password' => 'test123' ) );
    	$this->assertTrue(get_class($user) == 'TheTrainingMangerLMS\User');
    }

    function test_getCourses() {
    	$user = TheTrainingMangerLMS\User::create( array( 'login' => 'Joe Schmoe', 'password' => 'test123' ) );
    	$this->assertEquals(get_class($user), 'TheTrainingMangerLMS\User');

        $user = TheTrainingMangerLMS\Student::promote($user);
        $this->assertEquals(get_class($user), 'TheTrainingMangerLMS\Student');

    	$course = TheTrainingMangerLMS\OnlineCourse::create( array( 'title' => 'This is my favorite course!') );
    	$this->assertTrue(get_class($course) == 'TheTrainingMangerLMS\OnlineCourse');
    	$courses = $user->getCoursesList( array('NEVER') );
    	$this->assertEquals(0, count($courses));
    	$course->allowAccess($user, 'never');
    	$courses = $user->getCoursesList( array('NEVER') );
    	$this->assertEquals(1, count($courses));
    	$this->assertEquals($course->ID(), $courses[0]);
    	$course2 = TheTrainingMangerLMS\OnlineCourse::create( array( 'title' => 'This is another of my favorite courses!') );
    	$this->assertTrue(get_class($course2) == 'TheTrainingMangerLMS\OnlineCourse');

    	$course2->allowAccess($user, 'default');
    	$courses = $user->getCoursesList( array('ACTIVE') );
    	$this->assertEquals(1, count($courses));
    	$this->assertEquals($course2->ID(), $courses[0]);
    	$courses = $user->getCoursesList( array('ACTIVE','NEVER') );
    	$this->assertEquals(2, count($courses));
    	$this->assertTrue(in_array($course->ID(), $courses));
    	$this->assertTrue(in_array($course2->ID(), $courses));
    	$courses = $user->getCoursesList( 'all' );
    	$this->assertEquals(2, count($courses));
    	$this->assertTrue(in_array($course->ID(), $courses));
    	$this->assertTrue(in_array($course2->ID(), $courses));
    }

    function test_hasCourse() {
    	$user = TheTrainingMangerLMS\User::create( array( 'login' => 'Joe Schmoe', 'password' => 'test123' ) );
        $this->assertEquals(get_class($user), 'TheTrainingMangerLMS\User');

        $user = TheTrainingMangerLMS\Student::promote($user);
        $this->assertEquals(get_class($user), 'TheTrainingMangerLMS\Student');

    	$course = TheTrainingMangerLMS\OnlineCourse::create( array( 'title' => 'This is my favorite course!') );
    	$this->assertTrue(get_class($course) == 'TheTrainingMangerLMS\OnlineCourse');
    	$this->assertFalse($user->hasCourse($course));
    	$course->allowAccess($user, 'never');
    	$this->assertTrue($user->hasCourse($course));
    	$course->removeAccess($user);
    	$this->assertFalse($user->hasCourse($course));
    }

    function test_getCourseStatus() {
        $user = TheTrainingMangerLMS\User::create( array( 'login' => 'Joe Schmoe', 'password' => 'test123' ) );
        $this->assertEquals(get_class($user), 'TheTrainingMangerLMS\User');

        $user = TheTrainingMangerLMS\Student::promote($user);
        $this->assertEquals(get_class($user), 'TheTrainingMangerLMS\Student');

        $course = TheTrainingMangerLMS\OnlineCourse::create( array( 'title' => 'This is my favorite course!') );
        $this->assertTrue(get_class($course) == 'TheTrainingMangerLMS\OnlineCourse');
        $this->assertFalse($user->hasCourse($course));
        $course->allowAccess($user, 'never');
        $this->assertTrue($user->hasCourse($course));
        $this->assertEquals('NEVER', $user->getCourseStatus($course));
    }

}

?>
