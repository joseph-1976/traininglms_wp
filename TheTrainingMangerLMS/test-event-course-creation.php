<?php

require("wp-load.php");

//1st event - 1st workshop


/*

function createCourseAndEventsOLD($courseTitle, $eventInfo) {
$course = TheTrainingMangerLMS\LiveCourse::create( array( 'title' => 'This is my favorite course!') );

$start = \DateTime::createFromFormat('Y-m-d H:i:s', "2015-10-01 14:00:00");
$stop = clone $start; $stop->add(new \DateInterval("PT4H"));



$event = TheTrainingMangerLMS\LiveCourse\LiveCourseInstance::create(array());
$event->addWorkshop(new TheTrainingMangerLMS\Utility\Workshop(array( 'startDateTime' => $start, 'endDateTime' => $stop )));





//1st event - 2nd workshop
$start2 = \DateTime::createFromFormat('Y-m-d H:i:s', "2015-10-03 14:00:00");
$stop2 = clone $start2; $stop2->add(new \DateInterval("PT4H"));
$event->addWorkshop(new TheTrainingMangerLMS\Utility\Workshop(array( 'startDateTime' => $start2, 'endDateTime' => $stop2 )));



//1st event - 3rd workshop
$start3 = \DateTime::createFromFormat('Y-m-d H:i:s', "2015-10-02 14:00:00");
$stop3 = clone $start3; $stop3->add(new \DateInterval("PT4H"));

$event->addWorkshop(new TheTrainingMangerLMS\Utility\Workshop(array( 'startDateTime' => $start3, 'endDateTime' => $stop3 )));


//need to retrieve user rather than creating one
//$user = TheTrainingMangerLMS\User::create( array( 'login' => 'Joe Schm3oe', 'password' => 'test123' ) );

$event->setInstructor($user);

$event->setMaximumSeating(100);
$location = new TheTrainingMangerLMS\Utility\Location( array( 'city' => 'Asheville', 'state' => 'NC' ));
$event->setLocation($location);

}
*/





function create1stCourseandEvents() {

	$start1 = \DateTime::createFromFormat('Y-m-d H:i:s', "2015-10-01 14:00:00");
	$stop1 = clone $start1; $stop1->add(new \DateInterval("PT4H"));

	$start2 = \DateTime::createFromFormat('Y-m-d H:i:s', "2015-10-03 14:00:00");
	$stop2 = clone $start2; $stop2->add(new \DateInterval("PT4H"));
	$start3 = \DateTime::createFromFormat('Y-m-d H:i:s', "2015-10-02 14:00:00");
	$stop3 = clone $start3; $stop3->add(new \DateInterval("PT4H"));


	$workshops = array();
	$workshops[] = array('startDateTime' => $start1, 'endDateTime' => $stop1);
	$workshops[] = array('startDateTime' => $start2, 'endDateTime' => $stop2);
	$workshops[] = array('startDateTime' => $start3, 'endDateTime' => $stop3);




	$courseData = array();
	$courseData['title'] = "Basket Weaving (1 Event)";
	$courseData['default_event_price'] = 79.33;
	$courseData['regular_price'] = 179.33;
	$courseData['sale_price'] = 9.33;




	$eventData = array();
	$eventStart = \DateTime::createFromFormat('Y-m-d H:i:s', "2015-10-01 14:00:00");
	$eventStop = clone $eventStart; $eventStop->add(new \DateInterval("P2D"));



	$event = TheTrainingMangerLMS\LiveCourse\LiveCourseEvent::create(array('start_datetime' => $eventStart, 'stop_datetime' => $eventStop, 'price' => 77.23));
	$event->setMaximumSeating(100);


	//exit();

	$location = new TheTrainingMangerLMS\Utility\Location( array( 'city' => 'Asheville', 'state' => 'NC' ));
	$event->setLocation($location);
	$eventData[] = array('event' => $event, 'workshops' => $workshops);

	 


	createCourseAndEvents($courseData, $eventData);
}




function create2ndCourseAndEvents() {

	//2nd course with 4 events

	$courseData = array();
	$courseData['title'] = "Skateboarding 101 (4 Events)";
	$courseData['default_event_price'] = 64.25;
	$courseData['regular_price'] = 14.22;
	$courseData['sale_price'] = 6.99;


	//1st event
	$eventData = array();
	$eventStart = \DateTime::createFromFormat('Y-m-d H:i:s', "2015-12-01 14:00:00");
	$eventStop = clone $eventStart; $eventStop->add(new \DateInterval("P2D"));



	$event = TheTrainingMangerLMS\LiveCourse\LiveCourseEvent::create(array('start_datetime' => $eventStart, 'stop_datetime' => $eventStop));
	$event->setMaximumSeating(95);
	$location = new TheTrainingMangerLMS\Utility\Location( array( 'city' => 'Asheville', 'state' => 'NC' ));
	$event->setLocation($location);

	$workshops = array();
	$start1 = \DateTime::createFromFormat('Y-m-d H:i:s', "2015-12-01 14:00:00");
	$stop1 = clone $start1; $stop1->add(new \DateInterval("PT4H"));
	$start2 = \DateTime::createFromFormat('Y-m-d H:i:s', "2015-12-03 14:00:00");
	$stop2 = clone $start2; $stop2->add(new \DateInterval("PT4H"));

	$workshops[] = array('startDateTime' => $start1, 'endDateTime' => $stop1);
	$workshops[] = array('startDateTime' => $start2, 'endDateTime' => $stop2);



	$eventData[] = array('event' => $event, 'workshops' => $workshops);



	//2nd event

	$eventStart = \DateTime::createFromFormat('Y-m-d H:i:s', "2015-12-10 14:00:00");
	$eventStop = clone $eventStart; $eventStop->add(new \DateInterval("P3D"));


	$event = TheTrainingMangerLMS\LiveCourse\LiveCourseEvent::create(array('start_datetime' => $eventStart, 'stop_datetime' => $eventStop));
	$event->setMaximumSeating(50);
	$location = new TheTrainingMangerLMS\Utility\Location( array( 'city' => 'Asheville', 'state' => 'NC' ));
	$event->setLocation($location);


	$start1 = \DateTime::createFromFormat('Y-m-d H:i:s', "2015-12-10 14:00:00");
	$stop1 = clone $start1; $stop1->add(new \DateInterval("PT4H"));
	$start2 = \DateTime::createFromFormat('Y-m-d H:i:s', "2015-12-11 14:00:00");
	$stop2 = clone $start2; $stop2->add(new \DateInterval("PT4H"));
	$start3 = \DateTime::createFromFormat('Y-m-d H:i:s', "2015-12-12 14:00:00");
	$stop3 = clone $start3; $stop3->add(new \DateInterval("PT4H"));
	$start4 = \DateTime::createFromFormat('Y-m-d H:i:s', "2015-12-13 14:00:00");
	$stop4 = clone $start4; $stop4->add(new \DateInterval("PT4H"));
	
	

	$workshops = array();
	$workshops[] = array('startDateTime' => $start1, 'endDateTime' => $stop1);
	$workshops[] = array('startDateTime' => $start2, 'endDateTime' => $stop2);
	$workshops[] = array('startDateTime' => $start3, 'endDateTime' => $stop3);
	$workshops[] = array('startDateTime' => $start4, 'endDateTime' => $stop4);

	$eventData[] = array('event' => $event, 'workshops' => $workshops);







	//3rd event

	$eventStart = \DateTime::createFromFormat('Y-m-d H:i:s', "2015-06-01 14:00:00");
	$eventStop = clone $eventStart; $eventStop->add(new \DateInterval("P2D"));



	$event = TheTrainingMangerLMS\LiveCourse\LiveCourseEvent::create(array('start_datetime' => $eventStart, 'stop_datetime' => $eventStop));
	$event->setMaximumSeating(95);
	$location = new TheTrainingMangerLMS\Utility\Location( array( 'city' => 'Asheville', 'state' => 'NC' ));
	$event->setLocation($location);

	$workshops = array();
	$start1 = \DateTime::createFromFormat('Y-m-d H:i:s', "2015-06-01 14:00:00");
	$stop1 = clone $start1; $stop1->add(new \DateInterval("PT4H"));
	$start2 = \DateTime::createFromFormat('Y-m-d H:i:s', "2015-06-02 14:00:00");
	$stop2 = clone $start2; $stop2->add(new \DateInterval("PT4H"));

	$workshops[] = array('startDateTime' => $start1, 'endDateTime' => $stop1);
	$workshops[] = array('startDateTime' => $start2, 'endDateTime' => $stop2);


	
	$eventData[] = array('event' => $event, 'workshops' => $workshops);
	
	
	//4th event

	$eventStart = \DateTime::createFromFormat('Y-m-d H:i:s', "2015-07-21 14:00:00");
	$eventStop = clone $eventStart; $eventStop->add(new \DateInterval("P2D"));


	$event = TheTrainingMangerLMS\LiveCourse\LiveCourseEvent::create(array('start_datetime' => $eventStart, 'stop_datetime' => $eventStop));
	$event->setMaximumSeating(70);
	$location = new TheTrainingMangerLMS\Utility\Location( array( 'city' => 'Asheville', 'state' => 'NC' ));
	$event->setLocation($location);


	$start1 = \DateTime::createFromFormat('Y-m-d H:i:s', "2015-07-21 14:00:00");
	$stop1 = clone $start1; $stop1->add(new \DateInterval("PT4H"));
	$start2 = \DateTime::createFromFormat('Y-m-d H:i:s', "2015-07-23 14:00:00");
	$stop2 = clone $start2; $stop2->add(new \DateInterval("PT4H"));
	$start3 = \DateTime::createFromFormat('Y-m-d H:i:s', "2015-07-22 14:00:00");
	$stop3 = clone $start3; $stop3->add(new \DateInterval("PT4H"));


	$workshops = array();
	$workshops[] = array('startDateTime' => $start1, 'endDateTime' => $stop1);
	$workshops[] = array('startDateTime' => $start2, 'endDateTime' => $stop2);
	$workshops[] = array('startDateTime' => $start3, 'endDateTime' => $stop3);

	$eventData[] = array('event' => $event, 'workshops' => $workshops);
	createCourseAndEvents($courseData, $eventData);
}





function create3rdCourseAndEvents() {

	//3rd course with 3 events
	

	$courseData = array();
	$courseData['title'] = "Tacos For Dummies (3 Events)";
	$courseData['default_event_price'] = 134.25;
	$courseData['regular_price'] = 399.27;
	$courseData['sale_price'] = 199.99;


	//1st event - 1 day
	$eventData = array();
	$eventStart = \DateTime::createFromFormat('Y-m-d H:i:s', "2015-01-21 09:00:00");
	$eventStop = clone $eventStart; $eventStop->add(new \DateInterval("PT9H"));

	

	$event = TheTrainingMangerLMS\LiveCourse\LiveCourseEvent::create(array('start_datetime' => $eventStart, 'stop_datetime' => $eventStop));
	$event->setMaximumSeating(95);
	$location = new TheTrainingMangerLMS\Utility\Location( array( 'city' => 'Asheville', 'state' => 'NC' ));
	$event->setLocation($location);

	$workshops = array();
	$start1 = \DateTime::createFromFormat('Y-m-d H:i:s', "2015-01-21 09:00:00");
	$stop1 = clone $start1; $stop1->add(new \DateInterval("PT9H"));

	$workshops[] = array('startDateTime' => $start1, 'endDateTime' => $stop1);
	$eventData[] = array('event' => $event, 'workshops' => $workshops);


	//2nd event - 1 day
	
	
	$eventStart = \DateTime::createFromFormat('Y-m-d H:i:s', "2015-02-01 14:00:00");
	$eventStop = clone $eventStart; $eventStop->add(new \DateInterval("PT4H"));


	$event = TheTrainingMangerLMS\LiveCourse\LiveCourseEvent::create(array('start_datetime' => $eventStart, 'stop_datetime' => $eventStop));
	$event->setMaximumSeating(50);
	$location = new TheTrainingMangerLMS\Utility\Location( array( 'city' => 'Asheville', 'state' => 'NC' ));
	$event->setLocation($location);


	$start1 = \DateTime::createFromFormat('Y-m-d H:i:s', "2015-02-01 14:00:00");
	$stop1 = clone $start1; $stop1->add(new \DateInterval("PT4H"));

	$workshops = array();
	$workshops[] = array('startDateTime' => $start1, 'endDateTime' => $stop1);
	$eventData[] = array('event' => $event, 'workshops' => $workshops);


	//3rd event - 5 days
	$eventStart = \DateTime::createFromFormat('Y-m-d H:i:s', "2015-03-01 14:00:00");
	$eventStop = clone $eventStart; $eventStop->add(new \DateInterval("P5D"));

	$event = TheTrainingMangerLMS\LiveCourse\LiveCourseEvent::create(array('start_datetime' => $eventStart, 'stop_datetime' => $eventStop));
	$event->setMaximumSeating(95);
	$location = new TheTrainingMangerLMS\Utility\Location( array( 'city' => 'Asheville', 'state' => 'NC' ));
	$event->setLocation($location);

	
	
	$workshops = array();
	$start1 = \DateTime::createFromFormat('Y-m-d H:i:s', "2015-03-01 14:00:00");
	$stop1 = clone $start1; $stop1->add(new \DateInterval("PT4H"));
	$start2 = \DateTime::createFromFormat('Y-m-d H:i:s', "2015-03-02 14:00:00");
	$stop2 = clone $start2; $stop2->add(new \DateInterval("PT4H"));
	$start3 = \DateTime::createFromFormat('Y-m-d H:i:s', "2015-03-03 14:00:00");
	$stop3 = clone $start3; $stop3->add(new \DateInterval("PT4H"));
	$start4 = \DateTime::createFromFormat('Y-m-d H:i:s', "2015-03-04 14:00:00");
	$stop4 = clone $start4; $stop4->add(new \DateInterval("PT4H"));
	$start5 = \DateTime::createFromFormat('Y-m-d H:i:s', "2015-03-05 14:00:00");
	$stop5 = clone $start5; $stop5->add(new \DateInterval("PT4H"));
	$start5 = \DateTime::createFromFormat('Y-m-d H:i:s', "2015-03-06 14:00:00");
	$stop5 = clone $start5; $stop5->add(new \DateInterval("PT4H"));

	$workshops[] = array('startDateTime' => $start1, 'endDateTime' => $stop1);
	$workshops[] = array('startDateTime' => $start2, 'endDateTime' => $stop2);
	$workshops[] = array('startDateTime' => $start3, 'endDateTime' => $stop3);
	$workshops[] = array('startDateTime' => $start4, 'endDateTime' => $stop4);
	$workshops[] = array('startDateTime' => $start5, 'endDateTime' => $stop5);


	$eventData[] = array('event' => $event, 'workshops' => $workshops);

	
	createCourseAndEvents($courseData, $eventData);

}



create1stCourseAndEvents();
create2ndCourseAndEvents();
create3rdCourseAndEvents();


function createCourseAndEvents($courseData, $eventData, $debug = FALSE) {
	$user = TheTrainingMangerLMS\User::instance(get_current_user_id());
	
	$course = TheTrainingMangerLMS\LiveCourse::create( array('title' => $courseData['title'],  'default_event_price' => $courseData['default_event_price']));

	wp_publish_post($course->ID());
		
	foreach ($eventData as $eventArray) {
		$event = $eventArray['event'];
		if ($debug) {
			echo "iterating ....";
			echo "event id = ".$event->ID()."<br/>";
			//print_r($event);
			echo "<br/>";
		}
		foreach ($eventArray['workshops'] as $workshopData) {
			if ($debug) {
				echo "Workshop iterating for ".print_r($workshopData, TRUE)."<br/>";
			}
	
			wp_publish_post($event->ID());
			$event->addSeminar($workshopData['startDateTime'], $workshopData['endDateTime']);
		}
	
		$event->setInstructor($user);
		$course->addEvent($event);
		$targetProduct = $event->getAssociatedProduct();
		wp_publish_post($targetProduct->ID());
		
/*
		//To access an event's product
		$product2 = $event->getAssociatedProduct();
*/				
	}
	
	//post must specify date b/c WP gives it the default GMT time - so wont' publish for 5 hours from the time of creation
	$post_update = array('ID' => $course->ID(), 'post_date' => '2014-12-01 00:00:00');
	wp_update_post($post_update);

	
	echo "Course ID = ".$course->ID()." - ".$course->getTitle()."<br/>";
	echo "Course permalink = ".get_permalink($course->ID())."<br/>";
	echo "Sanity check.  This course has ".count($course->getUpcomingEventsAndProducts())." events and products.";
	echo "<br/><br/><br/>";
}

echo "DONE WITH CREATION";
?>