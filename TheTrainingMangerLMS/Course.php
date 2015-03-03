<?php
namespace TheTrainingMangerLMS;

/**
 * Training LMS base Course class
 *
 * This class encapsulates fields and functionality
 * specific to Training LMS Course.
 *
 **/
import('WP_DB_Object');
import('TheTrainingMangerLMS.Student');
import('TheTrainingMangerLMS.Lesson');
import('TheTrainingMangerLMS.Utility');
import('TheTrainingMangerLMS.CourseQuiz');
require_once "course_functions.php";

/*
	The class
*/
abstract class Course extends \WP_DB_Object {

// STUDENT ACCESS STATUSES
const ACTIVE  = "ACTIVE";
const EXPIRED = "EXPIRED";
const NEVER   = "NEVER";
protected static $access_statuses = array(self::ACTIVE, self::EXPIRED, self::NEVER);
public static function getAccessStatuses() {
	return self::$access_statuses;
}
// COURSE STATUSES
const DRAFT     = "DRAFT";
const REVIEW    = "REVIEW";
const PUBLISHED = "PUBLISHED";
protected static $course_statuses = array(self::DRAFT, self::REVIEW, self::PUBLISHED);
public static function getCourseStatuses() {
	return self::$course_statuses;
}
// DIFFICULTY LEVEL
const BEGINNER = "Beginner";
const INTERMEDIATE = "Intermediate";
const ADVANCED = "Advanced";
// Fields descriptor
protected static $fields = array(
	'title' => array( 'source' => 'post', 'wp_name' => 'post_title', 'required' => true ),
	'description' => array( 'source' => 'post', 'wp_name' => 'post_content' ),
	'short_description' => array( 'source' => 'post', 'wp_name' => 'post_excerpt' ),
	'created_by' => array( 'source' => 'post', 'wp_name' => 'post_author' ),
	'status' => array( 'source' => 'post', 'wp_name' => 'post_status' ),
	'lessons' => array( 'source' => 'postmeta', 'default' => array() ),
	'access_control_enabled' => array( 'source' => 'postmeta', 'default' => 'true' ),
	'default_access_period' => array( 'source' => 'postmeta', 'default' => '30' ),
	'level_of_difficulty' => array( 'source' => 'postmeta', 'default' => '' ),
	'objectives' => array( 'source' => 'postmeta', 'default' => '' ),
	'prerequisites' => array( 'source' => 'postmeta', 'default' => array() ),
//	'training_company' => array( 'source' => 'postmeta', 'default' => 0, 'type' => 'identifier', 'required' => false ),
	'open_forum' => array( 'source' => 'postmeta', 'default' => 0 ),
	'forum' => array( 'source' => 'postmeta', 'default' => 0 ), // associated_forum
	'featured' => array( 'source' => 'postmeta', 'default' => 'false' ),
	'signature_image' => array( 'source' => 'postmeta', 'default' => 0 ),
	'unassigned_topics' => array( 'source' => 'postmeta', 'default' => array() ),
	'passing_criterion' => array( 'source' => 'postmeta', 'default' => '' ),
	'default_passing_grade' => array( 'source' => 'postmeta', 'default' => '' ),
	'credits' => array( 'source' => 'postmeta', 'default' => '' ),
//	associated_media images, documents, etc  attachments
//	medium_descrition?
//	certificate?
	'quiz' => array( 'source' => 'postmeta', 'default' => 0 ),
	'manager' => array( 'source' => 'postmeta', 'default' => 0 )
);
protected static function getFieldsDescriptor() {
	return array_replace(parent::getFieldsDescriptor(), self::$fields);
}
protected static function prefix( $key ) {
	return ttp_lms_prefix('course_' . $key);
}
protected static function getPostType() {
	return ttp_lms_post_prefix(Constants::CoursePostType);
}

	/**
		Accessor methods
	**/
	public static function getLevelsOfDifficulty() {
	// Course implementations can override this as needed
		return array(self::BEGINNER, self::INTERMEDIATE, self::ADVANCED);
	}

	public static function getPassingCriteria() {
		return array("Online Final Exam", "Manual Entry of Grade", "Student Self-Assessment");
	}
/*
	Methods that can (and probably should) be synthesized via WP_DB_Object
*/
	public function getTitle() {
		return $this->title;
	}
	public function setTitle($title) {
		$this->title = $title;
	}

/**
   Lesson functions
**/
	public function getLessons() {
		$lessons = array();
		foreach($this->lessons as $lesson_id) {
			array_push($lessons, Lesson::instance($lesson_id));
		}
		return $lessons;
	}
	public function addLesson( Lesson $lesson ) {
		return $this->insertLesson(count($this->lessons), $lesson );
	}
	public function insertLesson($index, Lesson $lesson) {
		$lessons = $this->lessons; // copy to local context
		// make sure index is numeric
		if (!is_int($index))
			throw new \InvalidArgumentException("Index must be an integer.");
		// Validate index
		if (($index < 0) || ($index > count($lessons)))
			throw new \OutOfBoundsException("Index out of bounds.");
		// make sure the lesson isn't already added
		if (in_array($lesson->ID(), $lessons))
			throw new \InvalidArgumentException("Lesson already added.");
		array_splice($lessons, $index, 0, $lesson->ID());
		$this->lessons = $lessons;
	}
	public function removeLesson(Lesson $lesson) {
		$lessons = $this->lessons;
		$index = array_search($lesson->ID(), $lessons);
		if ($index === false)
			throw new \InvalidArgumentException("Lesson does not belong to this course.");
		// make sure index is numeric
		if (!is_int($index))
			throw new \InvalidArgumentException("Index must be an integer.");
		// Validate index
		if (($index < 0) || ($index >= count($this->lessons)))
			throw new \OutOfBoundsException("Index out of bounds.");
		//?get lesson we are removing
		//?$lesson = Lesson::instance($this->lessons[$index]);
		$this->lessons = array_merge(array_slice($this->lessons, 0, $index), 
			array_slice($this->lessons, $index+1, count($this->lessons)-1));
		//?return $lesson;
	}
	// updateLessonOrder(array $lessons)
	// removeAllLessons();

/*
   Access control functions
*/
	public function isAccessControlEnabled() {
		return $this->access_control_enabled == 'true';
	}
	public function enableAccessControl() {
		$this->setAccessControlEnabled(true);
	}
	public function setAccessControlEnabled( $enable = true ) {
		// TODO: check valid boolean:boolean to text?
		$this->access_control_enabled = ($enable === true ? 'true' : 'false');
		/*?clear access control lists for this course
		delete from usermeta where course_" . $course_id . "_expiration
		or set-up cron if enabled and disable otherwise*/
	}
	public function clearAccessControl() {
		/*?clear access control lists for this course from all users
		delete from usermeta where course_" . $course_id . "_expiration
		or set-up cron if enabled and disable otherwise*/
	}

/**
   User access control functions
**/
	public function allowAccess( Student $user, $period = 'never', $autoexpire = true) {
		// Optional Period in days, Period descriptor, or string constant; defaults to never
		// XOptional StartDate timestamp; defaults to Now
		// Optional AutoExpire; defaults to true
		// bomb if access control not enabled? LogicException
		//check if user has access (not expired or never trigger error)  "User has access; use modifyAccess instead";
		if ($this->canAccess( $user ))
			throw new \LogicException("Student has access; use modifyAccess() instead");
		if (is_int($period)) {
			if ($period < 1)
				throw new \InvalidArgumentException("Parameter period must be a positive integer");
			$period = array( 'unit' => 'DAY', 'interval' => $period);
		} elseif (is_array($period)) {
			if (!array_key_exists('unit', $period) || !array_key_exists('interval', $period))
				throw new \InvalidArgumentException("Parameter period has incorrect arguments");
			if (!in_array($period['unit'], Utility::UNITS))
				throw new \UnexpectedValueException("Parameter period->unit has unknown timeframe");
			if (!is_int($period['interval']))
				throw new \InvalidArgumentException("Parameter period->inverval must be a valid numeric value");
		} elseif (is_string($period)) {
			if (!in_array($period, array('never', 'default')))
				throw new \InvalidArgumentException("Paramter period has unknown argument");
			if ($period == 'default') {
				$period = array( 'unit' => 'DAY', 'interval' => $this->default_access_period );
			}
		}
		add_user_meta( $user->ID(), ttp_lms_prefix('course_' . $this->id), $this->id );
		$status = (($period == -1) || ($period == 'never')) ? self::NEVER : self::ACTIVE;
		add_user_meta( $user->ID(), ttp_lms_prefix('course_' . $this->id . '_status'), $status );
		if ($status == self::ACTIVE) {
			$expires = Utility::getExpirationTimeStamp($period['unit'], $period['interval']);
			add_user_meta( $user->ID(), ttp_lms_prefix('course_' . $this->id . '_expires'), $expires);
		}
		add_user_meta( $user->ID(), ttp_lms_prefix('course_current_lesson'), '1');
		if ($autoexpire) {
			// TODO: add new wp_cron entry?
		}
	}
	public function modifyAccess( Student $user, $period, $autoexpire = true ) {
		if (!$user->hasCourse($this))
			throw new \InvalidArgumentException("Student is not taking this course.");
		// TODO: finish
	}
	public function canAccess( Student $user ) {
		if (!$this->access_control_enabled) return true;
		return $user->hasCourse( $this ) && $this->accessExpired( $user );
	}
	public function timeRemaining(Student $user) { // seconds
		if (!$user->hasCourse($this))
			throw new \InvalidArgumentException("Student is not taking this course.");
		// get status
		$status = get_user_meta( $user->ID(), ttp_lms_prefix('course_' . $this->id . '_status'), true);
		if ($status == NEVER) return -1;
		elseif ($status == EXPIRED) return 0;
		global $wpdb;
		$remaining = $wpdb->get_var( $wpdb->prepare(
			"SELECT TIMESTAMPDIFF(SECOND, NOW(), TIMESTAMP(meta_value))
			FROM $wpdb->usermeta
			WHERE user_id = %d AND meta_key = %s",
			$user->ID(), ttp_lms_prefix('course_' . $this->id . '_expires')
		));
		return $remaining < 0 ? 0 : $remaining;
	}
	// convenience function
	public function timeRemainingDays(Student $user) {
		$time = $this->timeRemaining($user);
		$days = $time / (60 * 60 * 24);
		return (int)$days;
	}
	public function expireAccess(Student $user) {
		// we know it's both a valid user and valid course, check to see if user it taking the course
		if (!$user->hasCourse($this))
			throw new \InvalidArgumentException("Student is not taking this course.");
		update_user_meta($user->id, ttp_lms_prefix('course_' . $this->id . '_status'), 'expired');
	}
	public function hasAccessExpired(Student $user) {
		if (!$user->hasCourse($this))
			throw new \InvalidArgumentException("Student is not taking this course.");
		return get_user_meta($user->ID(), ttp_lms_prefix('course_' . $this->id . '_status'), true) != EXPIRED;
	}

/**
	Prerequisite functions.
**/
	public function getPrerequisites() {
		$prereqs = array();
		foreach($this->prerequisites as $course_id) {
			$courseType = static::getPostObjectType( $course_id );
			array_push($prereqs, $courseType::instance($course_id));
		}
		return $prereqs;
	}
	public function addPrerequisite(Course $course) {
		if ($this->id == $course->ID())
			throw new \InvalidArgumentException("Can't add course as its own prerequisite.");
		if (in_array($course->ID(), $this->prerequisites))
			throw new \InvalidArgumentException("Course is already a prerequisite.");
		$temp = $this->prerequisites;
		array_push($temp, $course->ID());
		$this->prerequisites = $temp;
	}
	public function removePrerequisite(Course $course) {
		$index = array_search($course->ID(), $this->prerequisites);
		if ($index === FALSE)
			throw new \InvalidArgumentException("Course is not a prerequisite.");
		// slice it up
		$this->prerequisites = array_merge(array_slice($this->prerequisites, 0, $index), 
			array_slice($this->prerequisites, $index+1, count($this->prerequisites)-1));
	}
	public function isCourseAPrerequisite(Course $course) {
		return in_array($course->ID(), $this->prerequisites);
	}

/**
	Level of Difficulty functions.
**/
	public function setLevelOfDifficulty($difficulty) {
		if (!in_array($difficulty, static::getLevelsOfDifficulty()))
			throw new \InvalidArgumentException("Invalid level of difficulty.");
		$this->level_of_difficulty = $difficulty;
	}
	public function getLevelOfDifficulty() {
		return $this->level_of_difficulty;
	}

/**
	Passing Criteria functions.
**/
	public function setPassingCriterion($passingCriterion) {
		if (!in_array($passingCriterion, static::getPassingCriteria()))
			throw new \InvalidArgumentException("Invalid passing criterion.");
		$this->passing_criterion = $passingCriterion;
	}
	public function getPassingCriterion() {
		return $this->passing_criterion;
	}
/**
	Miscellaneous user orientated functions.
**/
	public function getUsersList( $status = 'all' ) {
		global $wpdb;
		//Set = {'active', 'expired', 'never', 'all'}) 'all' or array(...) default 'all'
		if ($status == 'all') {
			$query = $wpdb->prepare(
				"SELECT IFNULL(GROUP_CONCAT(user_id SEPARATOR ','), '') as users FROM $wpdb->usermeta 
				WHERE meta_key = %s and meta_value = %d",
				ttp_lms_prefix('course_' . $this->id), $this->id);
		} elseif (is_array($status)) {
			foreach($status as $value) {
				if (!in_array($value, static::getAccessStatuses()))
					throw new \InvalidArgumentException("Unknown status {$value}.");
			}
			$query = $wpdb->prepare(
				"SELECT IFNULL(GROUP_CONCAT(user_id SEPARATOR ','), '') AS users FROM $wpdb->usermeta
				WHERE s.meta_key = %s AND FIND_IN_SET(s.meta_value, %s)",
				ttp_lms_prefix('course_' . $this->id . '_status'), implode(',', $filter)
			);
		} else {
			throw new \InvalidArgumentException("The filter argument is invalid.");
		}
		return explode(',', $wpdb->get_var($query));
	}
	public function removeAccess(Student $user) {
		// completely remove the course from the user's account
		if (!$user->hasCourse($this))
			throw new \InvalidArgumentException("Student is not taking this course.");
		delete_user_meta($user->ID(), ttp_lms_prefix('course_' . $this->id));
		delete_user_meta($user->ID(), ttp_lms_prefix('course_' . $this->id . '_status'));
		delete_user_meta($user->ID(), ttp_lms_prefix('course_' . $this->id . '_expires'));
		delete_user_meta($user->ID(), ttp_lms_prefix('course_' . $this->id . '_lesson'));
		//delete_user_meta($user->ID(), ttp_lms_prefix('course_' . $this->id . '_outcome'));
	}

	public function getEstimatedTime() {
		// try not to instantiate the Lesson's if at all possible
		$total = 0;
		foreach ($this->lessons as $lesson_id) {
			$total = $total + get_post_meta($lesson_id, ttp_lms_prefix('lesson_estimated_time'), true);
		}
		return $total;
	}

	public function getQuiz() {
		return $this->quiz == 0 ? NULL : Quiz::instance($this->quiz);
	}
	public function setQuiz(CourseQuiz $quiz) {
		$this->quiz = $quiz->ID();
		$quiz->setAssociatedCourse($this);
	}

	public function setFeatured($featured) {
		$this->featured = $featured === true ? 'true' : 'false';
	}
	public function isFeatured() {
		return $this->featured == 'true';
	}

	public function setSignatureImage($imageID) {
		$this->signature_image = $imageID;
	}
	public function getSignatureImage() {
		return $this->signature_image;
	}

	public function getUnassignedTopics() {
		$topics = array();
		foreach($this->unassigned_topics as $topic_id) {
			array_push($topics, Topic::instance($topic_id));
		}
		return $topics;
	}
	public function addUnassignedTopic(Topic $topic) {
		$topics = $this->unassigned_topics;
		if (in_array($topic->ID(), $topics))
			throw new \InvalidArgumentException("Topic already added.");
		array_push($topics, $topic->ID());
		$this->unassigned_topics = $topics;
	}
	public function removeUnassignedTopic(Topic $topic) {
		$topics = $this->unassigned_topics;
		$index = array_search($topic->ID(), $topics);
		if ($index === false)
			throw new \InvalidArgumentException("Topic is not an unassigned topic for this course.");

		$this->unassigned_topics = array_merge(array_slice($topics, 0, $index), 
			array_slice($topics, $index+1, count($topics)-1));
	}

	/**
	 * Return the trainer for a course
	 * @return mixed
	 */
	public function getManager() {
		return $this->manager;
	}

	/**
	 * Set the trainer for a course
	 * @param $value
	 */
	public function setManager( $manager ) { // most likely User $manager = null
		$this->manager = $manager;
	}

	public function getStatus() {
		$status = $this->status;
		if ($status == 'draft') {
			return self::DRAFT;
		} else if ($status == 'publish') {
			return self::PUBLISHED;
		}
	}

	public function setStatus( $status ) {
		if (!in_array($status, static::$course_statuses))
			throw new \InvalidArgumentException("The supplied status, " . $status . " is invalid.");

		if ($status == self::DRAFT) {
			$this->status = 'draft';
		} else if ($status == self::PUBLISHED) {
			$this->status = 'publish';
		}
	}

	public function getShortDescription() {
		return $this->short_description;
	}

	public function setShortDescription( $shortDescription ) {
		$this->short_description = $shortDescription;
	}

	public function getDescription() {
		return $this->description;
	}

	public function setDescription( $description ) {
		$this->description = $description;
	}

	public function getObjectives() {
		return $this->objectives;
	}

	public function setObjectives( $objectives ) {
		$this->objectives = $objectives;
	}

	/** Preliminary forum functions **/
/*	public function setAssociatedForum(Forum $forum) {
		$this->forum = $forum->ID();
	}
	public function getAssociatedForum() {
		return $this->forum ? Forum::instance($this->forum) : NULL;
	}
	public function setOpenForum(Forum $forum) {
		$this->open_forum = $forum->ID();
	}
	public function getAssociatedForum() {
		return $this->open_forum ? Forum::instance($this->open_forum) : NULL;
	}*/


/*	public function setTrainingCompany(\TheTrainingMangerLMS\TrainingCompany $company) {
		$this->training_company = is_null($company) ? 0 : $company->ID();
	}
	public function getTrainingCompany() {
		return $this->training_company == 0 ? NULL : \TheTrainingMangerLMS\TrainingCompany::instance($this->training_company);
	}*/

/*
	Media functions
	addMedia(name, type, URL)?
	getMedia(name);
	removeMedia
	listMedia();
*/
}

/*User[] accessList()
Instead of User, maybe Entity or User Group combo User_name, same for period (time, unit).
Need other functions for introducing a user to a class?
Set first lesson or currentLesson
Associate quizes with both lessons and courses

outcome: completed, incomplete, pass, fail?*/
?>
