<?php
namespace TheTrainingMangerLMS;

/**
 * Training LMS base Lesson class
 *
 * This class encapsulates fields and functionality
 * specific to Training LMS Lesson.
 *
 **/
import('WP_DB_Object');
import('TheTrainingMangerLMS.Topic');
import('TheTrainingMangerLMS.LessonQuiz');
require_once "lesson_functions.php";

/*
	The class
*/
abstract class Lesson extends \WP_DB_Object {
// Fields descriptor
protected static $fields = array(
	'title' => array( 'source' => 'post', 'wp_name' => 'post_title' ),
	'content' => array( 'source' => 'post', 'wp_name' => 'post_content' ),
	'author' => array( 'source' => 'post', 'wp_name' => 'post_author' ),
	'topics' => array( 'source' => 'postmeta', 'default' => array() ),
	'description' => array( 'source' => 'postmeta', 'default' => '' ),
	'estimated_time' => array( 'source' => 'postmeta', 'default' => 0 ),
	'quiz' => array( 'source' => 'postmeta', 'default' => 0 ),
	'associated_forum' => array( 'source' => 'postmeta', 'default' => 0 )
);
protected static function getFieldsDescriptor() {
	return array_replace(parent::getFieldsDescriptor(), self::$fields);
}
protected static function prefix( $key ) {
	return ttp_lms_prefix('lesson_' . $key);
}
protected static function getPostType() {
	return ttp_lms_post_prefix(Constants::LessonPostType);
}

/*
	Methods that can (and probably should) be synthesized via WP_DB_Object
	Technically speaking, all these functions can be synthesized with
	a type and collection attribute.
*/
public function getTitle() {
	return $this->title;
}
public function getDescription() {
	return $this->description;
}
public function getContent() {
	return $this->content;
}
/*
   Topic functions
*/
	public function getTopics() {
		$topics = array();
		foreach($this->topics as $topic_id) {
			//$topicType = "TheTrainingMangerLMS\\" . ttp_lms_topic_type( $topic_id );
			array_push($topics, Topic::instance($topic_id));
		}
		return $topics;
	}
	public function addTopic( Topic $topic ) {
		return $this->insertTopic(count($this->topics), $topic );
	}
	public function insertTopic($index, Topic $topic) {
		$topics = $this->topics; // copy to local context
		// make sure index is numeric
		if (!is_int($index))
			throw new \InvalidArgumentException("Index must be an integer.");
		// Validate index
		if (($index < 0) || ($index > count($topics)))
			throw new \OutOfBoundsException("Index out of bounds.");
		// make sure the topic isn't already added
		if (in_array($topic->ID(), $topics))
			throw new \InvalidArgumentException("Topic already added.");
		array_splice($topics, $index, 0, $topic->ID());
		$this->topics = $topics;
	}
	public function removeTopic( Topic $topic ) {
		$topics = $this->topics;
		$index = array_search($topic->ID(), $topics);
		if ($index === false)
			throw new \InvalidArgumentException("Topic does not belong to this lesson.");
		// make sure index is numeric
		if (!is_int($index))
			throw new \InvalidArgumentException("Index must be an integer.");
		// Validate index
		if (($index < 0) || ($index >= count($topics)))
			throw new \OutOfBoundsException("Index out of bounds.");
		$this->topics = array_merge(array_slice($this->topics, 0, $index), array_slice($this->topics, $index+1, count($this->topics)-1));
	}
	public function removeAllTopics() {
		$this->topics = array();
	}

	public function getQuiz() {
		return $this->quiz == 0 ? NULL : Quiz::instance($this->quiz);
	}
	public function setQuiz(LessonQuiz $quiz) {
		$this->quiz = $quiz->ID();
		$quiz->setAssociatedLesson($this);
	}

	public function getEstimatedTime() {
		return $this->estimated_time;
	}
	public function setEstimatedTime($estimated_time) {
		if (!is_int($estimated_time) && ($estimated_time < 0))
			throw new \InvalidArgumentException("Estimated time must be an integer and greater than 0.");
		$this->estimated_time = $estimated_time;
	}

	/*
	public function getForum() {
		return $this->associated_forum == 0 ? NULL : new \Forum($this->associated_forum);
	}
	public function setForum(Forum $forum) {
		$this->associated_forum = is_null($forum) ? 0 : $forum->ID();
	}*/
}

?>
