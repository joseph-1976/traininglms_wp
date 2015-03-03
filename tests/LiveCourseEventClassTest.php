<?php

class LiveCourseEventTest extends WP_UnitTestCase
{

	public function test_createLiveCourseEvent() {
    	$event = TheTrainingMangerLMS\LiveCourse\LiveCourseEvent::create(array());
    	$this->assertTrue(get_class($event) == 'TheTrainingMangerLMS\LiveCourse\LiveCourseEvent');
        $this->assertEquals('TheTrainingMangerLMS\LiveCourse\LiveCourseEvent', get_post_meta($event->ID(), ttp_lms_prefix('event_type'), true));
        //$event->addSeminar(new TheTrainingMangerLMS\Utility\Seminar(array( 'startDateTime' => $start, 'stopDateTime' => $stop )));
        $this->assertEquals(0, $event->getDuration());
        $this->assertTrue(is_null($event->getStartDateTime()));
        $this->assertTrue(is_null($event->getStopDateTime()));
	}

	/**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Unknown field name(s) foo in parameter list.
     */
    function test_createCourseWithBadParameters() {
    	$event = TheTrainingMangerLMS\LiveCourse\LiveCourseEvent::create( array( 'foo' => 'P4H' ) );
    }

    function test_addSeminar() {
        $start = \DateTime::createFromFormat('Y-m-d H:i:s', "2014-10-01 14:00:00");
        $stop = clone $start; $stop->add(new \DateInterval("PT4H"));
        $date = \DateTime::createFromFormat('Y-m-d H:i:s', "2014-10-01 00:00:00");
        $event = TheTrainingMangerLMS\LiveCourse\LiveCourseEvent::create(array());
        $this->assertTrue(get_class($event) == 'TheTrainingMangerLMS\LiveCourse\LiveCourseEvent');
        $this->assertEquals('TheTrainingMangerLMS\LiveCourse\LiveCourseEvent', get_post_meta($event->ID(), ttp_lms_prefix('event_type'), true));

        $event->addSeminar( $start, $stop );
        $this->assertEquals(1, $event->getDuration());
        $this->assertEquals($start, $event->getStartDateTime());
        $this->assertEquals($stop, $event->getStopDateTime());
    }

    function test_addMultipleSeminars() {
        $start = \DateTime::createFromFormat('Y-m-d H:i:s', "2014-10-01 14:00:00");
        $stop = clone $start; $stop->add(new \DateInterval("PT4H"));
        $date = \DateTime::createFromFormat('Y-m-d H:i:s', "2014-10-01 00:00:00");
        $event = TheTrainingMangerLMS\LiveCourse\LiveCourseEvent::create(array());
        $this->assertTrue(get_class($event) == 'TheTrainingMangerLMS\LiveCourse\LiveCourseEvent');
        $this->assertEquals('TheTrainingMangerLMS\LiveCourse\LiveCourseEvent', get_post_meta($event->ID(), ttp_lms_prefix('event_type'), true));

        $event->addSeminar( $start, $stop );
        $this->assertEquals(1, $event->getDuration());
        $this->assertEquals($start, $event->getStartDateTime());
        $this->assertEquals($stop, $event->getStopDateTime());

        $start2 = \DateTime::createFromFormat('Y-m-d H:i:s', "2014-10-03 14:00:00");
        $stop2 = clone $start2; $stop2->add(new \DateInterval("PT4H"));
        $date2 = \DateTime::createFromFormat('Y-m-d H:i:s', "2014-10-03 00:00:00");

        $event->addSeminar( $start2, $stop2 );
        $this->assertEquals(3, $event->getDuration());
        $this->assertEquals($start, $event->getStartDateTime());
        $this->assertEquals($stop2, $event->getStopDateTime());

        $start3 = \DateTime::createFromFormat('Y-m-d H:i:s', "2014-10-02 14:00:00");
        $stop3 = clone $start3; $stop3->add(new \DateInterval("PT4H"));
        $date3 = \DateTime::createFromFormat('Y-m-d H:i:s', "2014-10-02 00:00:00");

        $event->addSeminar( $start3, $stop3 );
        $this->assertEquals(3, $event->getDuration());
        $this->assertEquals($start, $event->getStartDateTime());
        $this->assertEquals($stop2, $event->getStopDateTime());
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Start and end dates must be on the same day.
     */
    function test_createSeminarWithBadTimes() {
        $start = \DateTime::createFromFormat('Y-m-d H:i:s', "2014-10-01 14:00:00");
        $stop = clone $start; $stop->add(new \DateInterval("PT24H"));
        $date = \DateTime::createFromFormat('Y-m-d H:i:s', "2014-10-01 00:00:00");
        $event = TheTrainingMangerLMS\LiveCourse\LiveCourseEvent::create(array());
        $this->assertTrue(get_class($event) == 'TheTrainingMangerLMS\LiveCourse\LiveCourseEvent');
        $this->assertEquals('TheTrainingMangerLMS\LiveCourse\LiveCourseEvent', get_post_meta($event->ID(), ttp_lms_prefix('event_type'), true));

        $event->addSeminar( $start, $stop );
    }

    function test_removeSeminar() {
        $start = \DateTime::createFromFormat('Y-m-d H:i:s', "2014-10-01 14:00:00");
        $stop = clone $start; $stop->add(new \DateInterval("PT4H"));
        $date = \DateTime::createFromFormat('Y-m-d H:i:s', "2014-10-01 00:00:00");
        $event = TheTrainingMangerLMS\LiveCourse\LiveCourseEvent::create(array());
        $this->assertTrue(get_class($event) == 'TheTrainingMangerLMS\LiveCourse\LiveCourseEvent');
        $this->assertEquals('TheTrainingMangerLMS\LiveCourse\LiveCourseEvent', get_post_meta($event->ID(), ttp_lms_prefix('event_type'), true));

        $event->addSeminar( $start, $stop );
        $this->assertEquals(1, $event->getDuration());
        $this->assertEquals($start, $event->getStartDateTime());
        $this->assertEquals($stop, $event->getStopDateTime());

        $start2 = \DateTime::createFromFormat('Y-m-d H:i:s', "2014-10-03 14:00:00");
        $stop2 = clone $start2; $stop2->add(new \DateInterval("PT4H"));
        $date2 = \DateTime::createFromFormat('Y-m-d H:i:s', "2014-10-03 00:00:00");

        $event->addSeminar( $start2, $stop2 );
        $this->assertEquals(3, $event->getDuration());
        $this->assertEquals($start, $event->getStartDateTime());
        $this->assertEquals($stop2, $event->getStopDateTime());

        $event->removeSeminar( $start, $stop );
        $this->assertEquals(1, $event->getDuration());
        $this->assertEquals($start2, $event->getStartDateTime());
        $this->assertEquals($stop2, $event->getStopDateTime());

        $event->removeSeminar( $start2, $stop2 );
        $this->assertEquals(0, $event->getDuration());
        $this->assertTrue(is_null($event->getStartDateTime()));
        $this->assertTrue(is_null($event->getStopDateTime()));
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage A seminar with the given date isn't a part of this event.
     */
    function test_removeNonExistantSeminar() {
        $start = \DateTime::createFromFormat('Y-m-d H:i:s', "2014-10-01 14:00:00");
        $stop = clone $start; $stop->add(new \DateInterval("PT4H"));
        $date = \DateTime::createFromFormat('Y-m-d H:i:s', "2014-10-01 00:00:00");
        $event = TheTrainingMangerLMS\LiveCourse\LiveCourseEvent::create(array());
        $this->assertTrue(get_class($event) == 'TheTrainingMangerLMS\LiveCourse\LiveCourseEvent');
        $this->assertEquals('TheTrainingMangerLMS\LiveCourse\LiveCourseEvent', get_post_meta($event->ID(), ttp_lms_prefix('event_type'), true));

        $event->addSeminar( $start, $stop );
        $this->assertEquals(1, $event->getDuration());
        $this->assertEquals($start, $event->getStartDateTime());
        $this->assertEquals($stop, $event->getStopDateTime());

        $start2 = \DateTime::createFromFormat('Y-m-d H:i:s', "2014-10-03 14:00:00");
        $stop2 = clone $start2; $stop2->add(new \DateInterval("PT4H"));
        $date2 = \DateTime::createFromFormat('Y-m-d H:i:s', "2014-10-03 00:00:00");

        $event->removeSeminar( $start2, $stop2 );
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage A seminar with this date has already been added.
     */
    function test_addMultipleSeminarsSameDay() {
        $start = \DateTime::createFromFormat('Y-m-d H:i:s', "2014-10-01 14:00:00");
        $stop = clone $start; $stop->add(new \DateInterval("PT4H"));
        $date = \DateTime::createFromFormat('Y-m-d H:i:s', "2014-10-01 00:00:00");
        $event = TheTrainingMangerLMS\LiveCourse\LiveCourseEvent::create(array());
        $this->assertTrue(get_class($event) == 'TheTrainingMangerLMS\LiveCourse\LiveCourseEvent');
        $this->assertEquals('TheTrainingMangerLMS\LiveCourse\LiveCourseEvent', get_post_meta($event->ID(), ttp_lms_prefix('event_type'), true));

        $event->addSeminar( $start, $stop );
        $event->addSeminar( $start, $stop );
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage A seminar with the given date isn't a part of this event.
     */
    function test_updateSeminarBadDay() {
        $start = \DateTime::createFromFormat('Y-m-d H:i:s', "2014-10-01 14:00:00");
        $stop = clone $start; $stop->add(new \DateInterval("PT4H"));
        $date = \DateTime::createFromFormat('Y-m-d H:i:s', "2014-10-01 00:00:00");
        $event = TheTrainingMangerLMS\LiveCourse\LiveCourseEvent::create(array());
        $this->assertTrue(get_class($event) == 'TheTrainingMangerLMS\LiveCourse\LiveCourseEvent');
        $this->assertEquals('TheTrainingMangerLMS\LiveCourse\LiveCourseEvent', get_post_meta($event->ID(), ttp_lms_prefix('event_type'), true));

        $event->addSeminar( $start, $stop );
        $this->assertEquals(1, $event->getDuration());
        $this->assertEquals($start, $event->getStartDateTime());
        $this->assertEquals($stop, $event->getStopDateTime());

        $start2 = \DateTime::createFromFormat('Y-m-d H:i:s', "2014-10-03 14:00:00");
        $stop2 = clone $start2; $stop2->add(new \DateInterval("PT4H"));
        $date2 = \DateTime::createFromFormat('Y-m-d H:i:s', "2014-10-03 00:00:00");

        $event->updateSeminar( $start2, $stop2 );
    }

    function test_datetimeFunctions() {
        $start = \DateTime::createFromFormat('Y-m-d H:i:s', "2014-10-01 14:00:00");
        $stop = clone $start; $stop->add(new \DateInterval("PT4H"));
        $date = \DateTime::createFromFormat('Y-m-d H:i:s', "2014-10-01 00:00:00");
        $event = TheTrainingMangerLMS\LiveCourse\LiveCourseEvent::create(array());
        $this->assertTrue(get_class($event) == 'TheTrainingMangerLMS\LiveCourse\LiveCourseEvent');
        $this->assertEquals('TheTrainingMangerLMS\LiveCourse\LiveCourseEvent', get_post_meta($event->ID(), ttp_lms_prefix('event_type'), true));

        $event->addSeminar( $start, $stop );
        $this->assertEquals(1, $event->getDuration());
        $this->assertEquals($start, $event->getStartDateTime());
        $this->assertEquals($stop, $event->getStopDateTime());

        $start2 = \DateTime::createFromFormat('Y-m-d H:i:s', "2014-10-03 14:00:00");
        $stop2 = clone $start2; $stop2->add(new \DateInterval("PT4H"));
        $date2 = \DateTime::createFromFormat('Y-m-d H:i:s', "2014-10-03 00:00:00");

        $event->addSeminar( $start2, $stop2 );
        $this->assertEquals(3, $event->getDuration());
        $this->assertEquals($start, $event->getStartDateTime());
        $this->assertEquals($stop2, $event->getStopDateTime());

        // now check manually
        $datetime_utc = get_post_meta($event->ID(), ttp_lms_prefix('event_start_datetime_utc'), true);
        $datetime_tz = get_post_meta($event->ID(), ttp_lms_prefix('event_start_datetime_tz'), true);
        $datetime = \DateTime::createFromFormat("Y-m-d H:i:s", $datetime_utc, new \DateTimeZone("UTC"));
        $datetime->setTimeZone(new \DateTimeZone($datetime_tz));
        $this->assertEquals($start, $datetime);

        $datetime_utc = get_post_meta($event->ID(), ttp_lms_prefix('event_stop_datetime_utc'), true);
        $datetime_tz = get_post_meta($event->ID(), ttp_lms_prefix('event_stop_datetime_tz'), true);
        $datetime = \DateTime::createFromFormat("Y-m-d H:i:s", $datetime_utc, new \DateTimeZone("UTC"));
        $datetime->setTimeZone(new \DateTimeZone($datetime_tz));
        $this->assertEquals($stop2, $datetime);
    }

    function test_hasPassedFunction() {
        $start = \DateTime::createFromFormat('Y-m-d H:i:s', "2014-10-01 14:00:00");
        $stop = clone $start; $stop->add(new \DateInterval("PT4H"));
        $date = \DateTime::createFromFormat('Y-m-d H:i:s', "2014-10-01 00:00:00");
        $event = TheTrainingMangerLMS\LiveCourse\LiveCourseEvent::create(array());
        $this->assertTrue(get_class($event) == 'TheTrainingMangerLMS\LiveCourse\LiveCourseEvent');
        $this->assertEquals('TheTrainingMangerLMS\LiveCourse\LiveCourseEvent', get_post_meta($event->ID(), ttp_lms_prefix('event_type'), true));

        $event->addSeminar( $start, $stop );
        $this->assertEquals(1, $event->getDuration());
        $this->assertEquals($start, $event->getStartDateTime());
        $this->assertEquals($stop, $event->getStopDateTime());

        $start2 = \DateTime::createFromFormat('Y-m-d H:i:s', "2014-10-03 14:00:00");
        $this->assertTrue($event->hasPassed($start2));
    }

    function test_inProgressFunction() {
        $start = \DateTime::createFromFormat('Y-m-d H:i:s', "2014-10-01 14:00:00");
        $stop = clone $start; $stop->add(new \DateInterval("PT4H"));
        $date = \DateTime::createFromFormat('Y-m-d H:i:s', "2014-10-01 00:00:00");
        $event = TheTrainingMangerLMS\LiveCourse\LiveCourseEvent::create(array());
        $this->assertTrue(get_class($event) == 'TheTrainingMangerLMS\LiveCourse\LiveCourseEvent');
        $this->assertEquals('TheTrainingMangerLMS\LiveCourse\LiveCourseEvent', get_post_meta($event->ID(), ttp_lms_prefix('event_type'), true));

        $event->addSeminar( $start, $stop );
        $this->assertEquals(1, $event->getDuration());
        $this->assertEquals($start, $event->getStartDateTime());
        $this->assertEquals($stop, $event->getStopDateTime());

        $start2 = \DateTime::createFromFormat('Y-m-d H:i:s', "2014-10-03 14:00:00");
        $stop2 = clone $start2; $stop2->add(new \DateInterval("PT4H"));
        $date2 = \DateTime::createFromFormat('Y-m-d H:i:s', "2014-10-03 00:00:00");

        $event->addSeminar( $start2, $stop2 );
        $this->assertEquals(3, $event->getDuration());
        $this->assertEquals($start, $event->getStartDateTime());
        $this->assertEquals($stop2, $event->getStopDateTime());

        $start3 = \DateTime::createFromFormat('Y-m-d H:i:s', "2014-10-02 14:00:00");
        $this->assertTrue($event->inProgress($start3));
    }

    function test_instructorFunctions() {
        $event = TheTrainingMangerLMS\LiveCourse\LiveCourseEvent::create(array());
        $this->assertTrue(get_class($event) == 'TheTrainingMangerLMS\LiveCourse\LiveCourseEvent');
        $this->assertEquals('TheTrainingMangerLMS\LiveCourse\LiveCourseEvent', get_post_meta($event->ID(), ttp_lms_prefix('event_type'), true));
        $user = TheTrainingMangerLMS\User::create( array( 'login' => 'Joe Schmoe', 'password' => 'test123' ) );
        $this->assertTrue(get_class($user) == 'TheTrainingMangerLMS\User');
        $event->setInstructor($user);
        $this->assertEquals($user->ID(), $event->getInstructor()->ID());
        // check manually
        $user_id = get_post_field('post_author', $event->ID(), 'db');
        $this->assertEquals($user->ID(), $user_id);
        $event->setInstructor(NULL);
        $this->assertTrue(is_null($event->getInstructor()));
        $user_id = get_post_field('post_author', $event->ID(), 'db');
        $this->assertEquals(0, $user_id);
    }

    function test_seatingFunctions() {
        $event = TheTrainingMangerLMS\LiveCourse\LiveCourseEvent::create(array());
        $this->assertTrue(get_class($event) == 'TheTrainingMangerLMS\LiveCourse\LiveCourseEvent');
        $event->setMaximumSeating(100);
        $this->assertEquals(100, $event->getMaximumSeating());
        // check manually
        $seating = get_post_meta($event->ID(), ttp_lms_prefix('event_maximum_seating'), true);
        $this->assertEquals(100, $seating);
    }

    function test_locationFunctions() {
        $start = new \DateTime();
        $stop = clone $start; $stop->add(new \DateInterval("PT4H"));
        $event = TheTrainingMangerLMS\LiveCourse\LiveCourseEvent::create( array( 'start_datetime' => $start, 'stop_datetime' => $stop ) );
        $this->assertTrue(get_class($event) == 'TheTrainingMangerLMS\LiveCourse\LiveCourseEvent');
        $event->setLocation(new TheTrainingMangerLMS\Utility\Location());
        $location = new TheTrainingMangerLMS\Utility\Location( array( 'city' => 'Asheville', 'state' => 'NC' ));
        $event->setLocation($location);
        $this->assertEquals($location, $event->getLocation());
        $this->assertEquals($location->city, 'Asheville');
        $this->assertEquals($location->state, 'NC');
    }

}
?>
