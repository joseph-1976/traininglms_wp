<?php

class LiveCourseClassTest extends WP_UnitTestCase
{

	public function test_createLiveCourse() {
    	$course = TheTrainingMangerLMS\LiveCourse::create( array( 'title' => 'This is my favorite course!') );
    	$this->assertTrue(get_class($course) == 'TheTrainingMangerLMS\LiveCourse');
        $this->assertEquals('This is my favorite course!', get_post_field('post_title', $course->ID(), 'db'));
        $this->assertEquals('TheTrainingMangerLMS\LiveCourse', get_post_meta($course->ID(), ttp_lms_prefix('course_type'), true));
    }

    public function test_getEvents() {
    	$course = TheTrainingMangerLMS\LiveCourse::create( array( 'title' => 'This is my favorite course!') );
    	$this->assertTrue(get_class($course) == 'TheTrainingMangerLMS\LiveCourse');
    	$events = $course->getEvents();
    	$this->assertEquals(0, count($events));

        $start = \DateTime::createFromFormat('Y-m-d H:i:s', "2014-10-01 14:00:00");
        $stop = clone $start; $stop->add(new \DateInterval("PT4H"));
        $date = \DateTime::createFromFormat('Y-m-d H:i:s', "2014-10-01 00:00:00");
    	$event = TheTrainingMangerLMS\LiveCourse\LiveCourseEvent::create( array(  ) );
    	$this->assertTrue(get_class($event) == 'TheTrainingMangerLMS\LiveCourse\LiveCourseEvent');
        $event->addSeminar( $start, $stop );
        $this->assertEquals(1, $event->getDuration());
        $this->assertEquals($start, $event->getStartDateTime());
        $this->assertEquals($stop, $event->getStopDateTime());

    	$course->addEvent($event);
    	$events = $course->getEvents();
    	$this->assertEquals(1, count($events));
    	$this->assertEquals($event->ID(), $events[0]->ID());
    	$this->assertEquals($event->getStartDateTime(), $events[0]->getStartDateTime());
    	$this->assertEquals($event->getStopDateTime(), $events[0]->getStopDateTime());
    	$this->assertEquals($event->getDuration(), $events[0]->getDuration());

        $start2 = \DateTime::createFromFormat('Y-m-d H:i:s', "2014-10-03 14:00:00");
        $stop2 = clone $start2; $stop2->add(new \DateInterval("PT4H"));
        $date2 = \DateTime::createFromFormat('Y-m-d H:i:s', "2014-10-03 00:00:00");

        $event2 = TheTrainingMangerLMS\LiveCourse\LiveCourseEvent::create( array() );
        $event2->addSeminar( $start2, $stop2 );
        $this->assertEquals(1, $event2->getDuration());
        $this->assertEquals($start2, $event2->getStartDateTime());
        $this->assertEquals($stop2, $event2->getStopDateTime());

    	$course->addEvent($event2);
    	$events = $course->getEvents();
    	$this->assertEquals(2, count($events));

        $start3 = \DateTime::createFromFormat('Y-m-d H:i:s', "2014-10-02 14:00:00");

    	$events = $course->getEvents($start3);
    	$this->assertEquals(1, count($events));
        $this->assertEquals( $events[0]->ID(), $event2->ID() );

        $events = $course->getEvents($start);
        $this->assertEquals(2, count($events));

        $events = $course->getEvents($start, $start);
        $this->assertEquals(1, count($events));
        $this->assertEquals( $events[0]->ID(), $event->ID() );

        $start2->add(new \DateInterval("PT24H"));
        $events = $course->getEvents($start2);
        $this->assertEquals(0, count($events));
    }

	/**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage From date must be before to date.
     */
    public function test_getEventsInvertedDates() {
    	$course = TheTrainingMangerLMS\LiveCourse::create( array( 'title' => 'This is my favorite course!') );
    	$this->assertTrue(get_class($course) == 'TheTrainingMangerLMS\LiveCourse');
		$temp = new \DateTime();
		$course->getEvents(clone $temp, $temp->sub(new \DateInterval("P1D")));
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Event is already scheduled.
     */
    public function test_addEventSameEvent() {
        $course = TheTrainingMangerLMS\LiveCourse::create( array( 'title' => 'This is my favorite course!') );
        $this->assertTrue(get_class($course) == 'TheTrainingMangerLMS\LiveCourse');
        $temp = new \DateTime(); $stop = clone $temp; $stop->add(new \DateInterval('PT4H'));
        $event = TheTrainingMangerLMS\LiveCourse\LiveCourseEvent::create( array( 'start_datetime' => $temp, 'stop_datetime' => $stop ) );
        $this->assertTrue(get_class($event) == 'TheTrainingMangerLMS\LiveCourse\LiveCourseEvent');
        $course->addEvent($event);
        $course->addEvent($event);
    }

    public function test_removeEvent() {
        $course = TheTrainingMangerLMS\LiveCourse::create( array( 'title' => 'This is my favorite course!') );
        $this->assertTrue(get_class($course) == 'TheTrainingMangerLMS\LiveCourse');
        $event = TheTrainingMangerLMS\LiveCourse\LiveCourseEvent::create( array() );
        $this->assertTrue(get_class($event) == 'TheTrainingMangerLMS\LiveCourse\LiveCourseEvent');
        $course->addEvent($event);
        $events = $course->getEvents();
        $this->assertEquals(1, count($events));
        $this->assertEquals($event->ID(), $events[0]->ID());

        $start2 = \DateTime::createFromFormat("Y-m-d H:i:s", "2014-11-02 10:45:00"); 
        $stop2 = clone $start2; $stop2->add(new \DateInterval('PT1H'));
        $event2 = TheTrainingMangerLMS\LiveCourse\LiveCourseEvent::create( array(  ) );
        $this->assertTrue(get_class($event2) == 'TheTrainingMangerLMS\LiveCourse\LiveCourseEvent');
        $event2->addSeminar( $start2, $stop2 );
        $course->addEvent($event2);
        $events = $course->getEvents();
        $this->assertEquals(2, count($events));
        $course->removeEvent($event);
        $events = $course->getEvents();
        $this->assertEquals(1, count($events));
        $this->assertEquals($event2->ID(), $events[0]->ID());
        $course->removeEvent($event2);
        $events = $course->getEvents();
        $this->assertEquals(0, count($events));
    }

    public function test_assignInstance() {
        $course = TheTrainingMangerLMS\LiveCourse::create( array( 'title' => 'This is my favorite course!') );
        $this->assertTrue(get_class($course) == 'TheTrainingMangerLMS\LiveCourse');
        $temp = new \DateTime(); $stop = clone $temp; $stop->add(new \DateInterval('PT4H'));
        $event = TheTrainingMangerLMS\LiveCourse\LiveCourseEvent::create( array( 'start_datetime' => $temp, 'stop_datetime' => $stop, 'maximum_seating' => 100 ) );
        $this->assertTrue(get_class($event) == 'TheTrainingMangerLMS\LiveCourse\LiveCourseEvent');
        $course->addEvent($event);
        $temp2 = \DateTime::createFromFormat("Y-m-d H:i:s", "2014-11-02 10:45:00"); $stop2 = clone $temp2; $stop2->add(new \DateInterval('PT1H'));
        $event2 = TheTrainingMangerLMS\LiveCourse\LiveCourseEvent::create( array( 'start_datetime' => $temp2, 'stop_datetime' => $stop2 ) );
        $this->assertTrue(get_class($event2) == 'TheTrainingMangerLMS\LiveCourse\LiveCourseEvent');
        $course->addEvent($event2);
        // create user
        $user = TheTrainingMangerLMS\User::create( array( 'login' => 'Joe Schmoe', 'password' => 'test123' ) );
        $this->assertEquals(get_class($user), 'TheTrainingMangerLMS\User');
        $user = TheTrainingMangerLMS\Student::promote($user);
        $this->assertEquals(get_class($user), 'TheTrainingMangerLMS\Student');
        // allow user to asscess course
        $course->allowAccess($user, 'never');
        // assign event to user
        $course->assignInstance($event, $user);
        // verify assignment
        $event3 = $course->getScheduledInstance($user);
        $this->assertEquals($event3->ID(), $event->ID());
        // verify assignment manually
        $this->assertEquals($event->ID(), get_user_meta($user->ID(), ttp_lms_prefix('course_' . $course->ID() . '_event_' . $event->ID()), true));
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Student is not taking this course.
     */
    public function test_assignInstanceWithoutUserAccess() {
        $course = TheTrainingMangerLMS\LiveCourse::create( array( 'title' => 'This is my favorite course!') );
        $this->assertTrue(get_class($course) == 'TheTrainingMangerLMS\LiveCourse');
        $temp = new \DateTime(); $stop = clone $temp; $stop->add(new \DateInterval('PT4H'));
        $event = TheTrainingMangerLMS\LiveCourse\LiveCourseEvent::create( array( 'start_datetime' => $temp, 'stop_datetime' => $stop, 'maximum_seating' => 100 ) );
        $this->assertTrue(get_class($event) == 'TheTrainingMangerLMS\LiveCourse\LiveCourseEvent');
        $course->addEvent($event);
        $user = TheTrainingMangerLMS\User::create( array( 'login' => 'Joe Schmoe', 'password' => 'test123' ) );
        $this->assertEquals(get_class($user), 'TheTrainingMangerLMS\User');
        $user = TheTrainingMangerLMS\Student::promote($user);
        $this->assertEquals(get_class($user), 'TheTrainingMangerLMS\Student');
        $course->assignInstance($event, $user);     
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Student has already scheduled a class for this course.
     */
    public function test_assignInstanceAgain() {
        $course = TheTrainingMangerLMS\LiveCourse::create( array( 'title' => 'This is my favorite course!') );
        $this->assertTrue(get_class($course) == 'TheTrainingMangerLMS\LiveCourse');
        $temp = new \DateTime(); $stop = clone $temp; $stop->add(new \DateInterval('PT4H'));
        $event = TheTrainingMangerLMS\LiveCourse\LiveCourseEvent::create( array( 'start_datetime' => $temp, 'stop_datetime' => $stop, 'maximum_seating' => 100 ) );
        $this->assertTrue(get_class($event) == 'TheTrainingMangerLMS\LiveCourse\LiveCourseEvent');
        $course->addEvent($event);
        $user = TheTrainingMangerLMS\User::create( array( 'login' => 'Joe Schmoe', 'password' => 'test123' ) );
        $this->assertEquals(get_class($user), 'TheTrainingMangerLMS\User');
        $user = TheTrainingMangerLMS\Student::promote($user);
        $this->assertEquals(get_class($user), 'TheTrainingMangerLMS\Student');
        $course->allowAccess($user, 'never');
        $course->assignInstance($event, $user);     
        $course->assignInstance($event, $user);     
    }

    public function test_unassignInstance() {
        $course = TheTrainingMangerLMS\LiveCourse::create( array( 'title' => 'This is my favorite course!') );
        $this->assertTrue(get_class($course) == 'TheTrainingMangerLMS\LiveCourse');
        $temp = new \DateTime(); $stop = clone $temp; $stop->add(new \DateInterval('PT4H'));
        $event = TheTrainingMangerLMS\LiveCourse\LiveCourseEvent::create( array( 'start_datetime' => $temp, 'stop_datetime' => $stop, 'maximum_seating' => 100 ) );
        $this->assertTrue(get_class($event) == 'TheTrainingMangerLMS\LiveCourse\LiveCourseEvent');
        $course->addEvent($event);
        $user = TheTrainingMangerLMS\User::create( array( 'login' => 'Joe Schmoe', 'password' => 'test123' ) );
        $this->assertEquals(get_class($user), 'TheTrainingMangerLMS\User');
        $user = TheTrainingMangerLMS\Student::promote($user);
        $this->assertEquals(get_class($user), 'TheTrainingMangerLMS\Student');
        $course->allowAccess($user, 'never');
        $course->assignInstance($event, $user);     
        $this->assertEquals($course->getScheduledInstance($user)->ID(), $event->ID());
        $course->unassignInstance($event, $user);
        $this->assertFalse($course->hasScheduledInstance($user));
    }

    // unassignclass without user access

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Student has not scheduled this event.
     */
    public function test_unassignInstanceUserNotScheduled() {
        $course = TheTrainingMangerLMS\LiveCourse::create( array( 'title' => 'This is my favorite course!') );
        $this->assertTrue(get_class($course) == 'TheTrainingMangerLMS\LiveCourse');
        $temp = new \DateTime(); $stop = clone $temp; $stop->add(new \DateInterval('PT4H'));
        $event = TheTrainingMangerLMS\LiveCourse\LiveCourseEvent::create( array( 'start_datetime' => $temp, 'stop_datetime' => $stop, 'maximum_seating' => 100 ) );
        $this->assertTrue(get_class($event) == 'TheTrainingMangerLMS\LiveCourse\LiveCourseEvent');
        $course->addEvent($event);
        $user = TheTrainingMangerLMS\User::create( array( 'login' => 'Joe Schmoe', 'password' => 'test123' ) );
        $this->assertEquals(get_class($user), 'TheTrainingMangerLMS\User');
        $user = TheTrainingMangerLMS\Student::promote($user);
        $this->assertEquals(get_class($user), 'TheTrainingMangerLMS\Student');
        $course->allowAccess($user, 'never');
        $course->unassignInstance($event, $user);
    }

    public function test_hasScheduledInstance() {
    }
    public function test_getScheduledInstancce() {
    }

    public function test_getSeatsRemaining() {
        $course = TheTrainingMangerLMS\LiveCourse::create( array( 'title' => 'This is my favorite course!') );
        $this->assertTrue(get_class($course) == 'TheTrainingMangerLMS\LiveCourse');
        $temp = new \DateTime(); $stop = clone $temp; $stop->add(new \DateInterval('PT4H'));
        $event = TheTrainingMangerLMS\LiveCourse\LiveCourseEvent::create( array( 'start_datetime' => $temp, 'stop_datetime' => $stop, 'maximum_seating' => 100 ) );
        $this->assertTrue(get_class($event) == 'TheTrainingMangerLMS\LiveCourse\LiveCourseEvent');
        $course->addEvent($event);
        $user = TheTrainingMangerLMS\User::create( array( 'login' => 'Joe Schmoe', 'password' => 'test123' ) );
        $this->assertEquals(get_class($user), 'TheTrainingMangerLMS\User');
        $user = TheTrainingMangerLMS\Student::promote($user);
        $this->assertEquals(get_class($user), 'TheTrainingMangerLMS\Student');

        $course->allowAccess($user, 'never');
        $remaining = $course->getSeatsRemaining($event);
        $this->assertEquals(100, $remaining);
        $course->assignInstance($event, $user);     
        $remaining = $course->getSeatsRemaining($event);
        $this->assertEquals(99, $remaining);
        $course->unassignInstance($event, $user);
        $remaining = $course->getSeatsRemaining($event);
        $this->assertEquals(100, $remaining);
    }

    public function test_getUsersListInInstance() {
        $course = TheTrainingMangerLMS\LiveCourse::create( array( 'title' => 'This is my favorite course!') );
        $this->assertTrue(get_class($course) == 'TheTrainingMangerLMS\LiveCourse');
        $temp = new \DateTime(); $stop = clone $temp; $stop->add(new \DateInterval('PT4H'));
        $event = TheTrainingMangerLMS\LiveCourse\LiveCourseEvent::create( array( 'start_datetime' => $temp, 'stop_datetime' => $stop, 'maximum_seating' => 100 ) );
        $this->assertTrue(get_class($event) == 'TheTrainingMangerLMS\LiveCourse\LiveCourseEvent');
        $course->addEvent($event);
        $user = TheTrainingMangerLMS\User::create( array( 'login' => 'Joe Schmoe', 'password' => 'test123' ) );
        $this->assertTrue(get_class($user) == 'TheTrainingMangerLMS\User');
        $user = TheTrainingMangerLMS\Student::promote($user);
        $user2 = TheTrainingMangerLMS\User::create( array( 'login' => 'Sarah Jane', 'password' => 'test123' ) );
        $this->assertTrue(get_class($user2) == 'TheTrainingMangerLMS\User');
        $user2 = TheTrainingMangerLMS\Student::promote($user2);
        $course->allowAccess($user, 'never');
        $course->allowAccess($user2, 'never');
        $user_list = $course->getUsersListInInstance($event);
        $this->assertEquals(0, count($user_list));
        $course->assignInstance($event, $user);
        $course->assignInstance($event, $user2);
        $user_list = $course->getUsersListInInstance($event);
        $this->assertTrue(in_array($user->ID(), $user_list));
        $this->assertTrue(in_array($user2->ID(), $user_list));
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Live courses can only have one lesson.
     */
    public function test_addLesson() {
        $course = TheTrainingMangerLMS\LiveCourse::create( array( 'title' => 'This is my favorite course!') );
        $this->assertTrue(get_class($course) == 'TheTrainingMangerLMS\LiveCourse');
        $lesson = TheTrainingMangerLMS\OnlineLesson::create( array( 'title' => 'This is my favorite lesson!') );
        $this->assertTrue(get_class($lesson) == 'TheTrainingMangerLMS\OnlineLesson');
        $lesson2 = TheTrainingMangerLMS\OnlineLesson::create( array( 'title' => 'This is my least favorite lesson!') );
        $this->assertTrue(get_class($lesson2) == 'TheTrainingMangerLMS\OnlineLesson');
        $course->addLesson($lesson);
        $lessons = $course->getLessons();
        $this->assertEquals(1, count($lessons));
        $course->addLesson($lesson2);
    }

    public function test_addEventProductAssociation() {
        $course = TheTrainingMangerLMS\LiveCourse::create( array( 'title' => 'This is my favorite course!') );
        $this->assertTrue(get_class($course) == 'TheTrainingMangerLMS\LiveCourse');
        $events = $course->getEvents();
        $this->assertEquals(0, count($events));

        $course->setDefaultEventPrice("13.00");

        $start = \DateTime::createFromFormat('Y-m-d H:i:s', "2014-10-01 14:00:00");
        $stop = clone $start; $stop->add(new \DateInterval("PT4H"));
        $date = \DateTime::createFromFormat('Y-m-d H:i:s', "2014-10-01 00:00:00");
        $event = TheTrainingMangerLMS\LiveCourse\LiveCourseEvent::create( array(  ) );
        $this->assertTrue(get_class($event) == 'TheTrainingMangerLMS\LiveCourse\LiveCourseEvent');
        $event->addSeminar( $start, $stop );
        $this->assertEquals(1, $event->getDuration());
        $this->assertEquals($start, $event->getStartDateTime());
        $this->assertEquals($stop, $event->getStopDateTime());

        $course->addEvent($event);

        $product = \TheTrainingMangerLMS\Utility::getAssociatedProduct('_event_id', $event->ID());
        $this->assertFalse(is_null($product));
        $this->assertEquals("13.00", $product->getPrice());
    }

    public function test_updateTitleInEventProduct() {
        $course = TheTrainingMangerLMS\LiveCourse::create( array( 'title' => 'This is my favorite course!') );
        $this->assertTrue(get_class($course) == 'TheTrainingMangerLMS\LiveCourse');
        $events = $course->getEvents();
        $this->assertEquals(0, count($events));

        $start = \DateTime::createFromFormat('Y-m-d H:i:s', "2014-10-01 14:00:00");
        $stop = clone $start; $stop->add(new \DateInterval("PT4H"));
        $date = \DateTime::createFromFormat('Y-m-d H:i:s', "2014-10-01 00:00:00");
        $event = TheTrainingMangerLMS\LiveCourse\LiveCourseEvent::create( array(  ) );
        $this->assertTrue(get_class($event) == 'TheTrainingMangerLMS\LiveCourse\LiveCourseEvent');
        $event->addSeminar( $start, $stop );
        $this->assertEquals(1, $event->getDuration());
        $this->assertEquals($start, $event->getStartDateTime());
        $this->assertEquals($stop, $event->getStopDateTime());

        $course->addEvent($event);

        $product = \TheTrainingMangerLMS\Utility::getAssociatedProduct('_event_id', $event->ID());
        $this->assertFalse(is_null($product));
        $this->assertEquals($course->getTitle(), $product->getTitle());

        $course->setTitle('Updated title!');
        $product = \TheTrainingMangerLMS\Utility::getAssociatedProduct('_event_id', $event->ID());
        $this->assertFalse(is_null($product));
        $this->assertEquals($course->getTitle(), $product->getTitle());    
    }

    public function test_officialTrainerGetAddRemoveFunctions() {
        $course = TheTrainingMangerLMS\LiveCourse::create( array( 'title' => 'This is my favorite course!') );
        $this->assertTrue(get_class($course) == 'TheTrainingMangerLMS\LiveCourse');

        $trainers = $course->getOfficialTrainers();
        $this->assertEquals(0, count($trainers));

        // create user
        $user = TheTrainingMangerLMS\User::create( array( 'login' => 'Joe Schmoe', 'password' => 'test123' ) );
        $this->assertTrue(get_class($user) == 'TheTrainingMangerLMS\User');
        $user = TheTrainingMangerLMS\Trainer::promote($user);

        $course->addOfficialTrainer($user);
        $trainers = $course->getOfficialTrainers();
        $this->assertEquals(1, count($trainers));
        $this->assertEquals($user->ID(), $trainers[0]->ID());

        // create user
        $user2 = TheTrainingMangerLMS\User::create( array( 'login' => 'Sarah Jane', 'password' => 'test123' ) );
        $this->assertTrue(get_class($user2) == 'TheTrainingMangerLMS\User');
        $user2 = TheTrainingMangerLMS\Trainer::promote($user2);

        $course->addOfficialTrainer($user2);
        $trainers = $course->getOfficialTrainers();
        $this->assertEquals(2, count($trainers));
        $this->assertEquals($user2->ID(), $trainers[1]->ID());

        $course->removeOfficialTrainer($user);
        $trainers = $course->getOfficialTrainers();
        $this->assertEquals(1, count($trainers));
        $this->assertEquals($user2->ID(), $trainers[0]->ID());

        $course->removeOfficialTrainer($user2);
        $trainers = $course->getOfficialTrainers();
        $this->assertEquals(0, count($trainers));
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage The Trainer is already an official of this Course.
     */
    public function test_officialTrainerAddAlreadyExist() {
        $course = TheTrainingMangerLMS\LiveCourse::create( array( 'title' => 'This is my favorite course!') );
        $this->assertTrue(get_class($course) == 'TheTrainingMangerLMS\LiveCourse');

        // create user
        $user = TheTrainingMangerLMS\User::create( array( 'login' => 'Joe Schmoe', 'password' => 'test123' ) );
        $this->assertTrue(get_class($user) == 'TheTrainingMangerLMS\User');
        $trainer = TheTrainingMangerLMS\Trainer::promote($user);

        $course->addOfficialTrainer($trainer);
        $course->addOfficialTrainer($trainer);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage The trainer is not an official of this Course.
     */
    public function test_officialTrainerRemoveNonExistant() {
        $course = TheTrainingMangerLMS\LiveCourse::create( array( 'title' => 'This is my favorite course!') );
        $this->assertTrue(get_class($course) == 'TheTrainingMangerLMS\LiveCourse');

        // create user
        $user = TheTrainingMangerLMS\User::create( array( 'login' => 'Joe Schmoe', 'password' => 'test123' ) );
        $this->assertTrue(get_class($user) == 'TheTrainingMangerLMS\User');

        $trainer = TheTrainingMangerLMS\Trainer::promote($user);

        $course->addOfficialTrainer($trainer);

        // create user
        $user2 = TheTrainingMangerLMS\User::create( array( 'login' => 'Sarah Jane', 'password' => 'test123' ) );
        $this->assertTrue(get_class($user2) == 'TheTrainingMangerLMS\User');

        $trainer2 = TheTrainingMangerLMS\Trainer::promote($user2);

        $course->removeOfficialTrainer($trainer2);
    }
}

?>
