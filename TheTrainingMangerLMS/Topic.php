<?php
namespace TheTrainingMangerLMS;

/**
 * Training LMS base Topic class
 *
 * This class encapsulates fields and functionality
 * specific to Training LMS Topic.
 *
 */
import('WP_DB_Object');
require_once "topic_functions.php";

/*
	The class
*/
class Topic extends \WP_DB_Object {
// Fields descriptor
protected static $fields = array(
	'title' => array( 'source' => 'post', 'wp_name' => 'post_title' ),
	'content' => array( 'source' => 'post', 'wp_name' => 'post_content' ),
	'content_type' => array( 'source' => 'post', 'wp_name' => 'post_mime_type' ),
	'author' => array( 'source' => 'post', 'wp_name' => 'post_author' ),
);
protected static function getFieldsDescriptor() {
	return self::$fields;
}
protected static function prefix( $key ) {
	return ttp_lms_prefix('topic_' . $key);
}
protected static function getPostType() {
	return ttp_lms_post_prefix(Constants::TopicPostType);
}

	public static function getContentTypes() {
		return array('Html','Video');
	}

	public function getTitle() {
		return $this->title;
	}
	public function setTitle($title) {
		$this->title = $title;
	}

	public function getContentType() {
		return $this->content_type;
	}
}

?>
