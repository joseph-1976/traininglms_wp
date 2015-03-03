<?php
namespace TheTrainingMangerLMS\LiveCourse;
use TheTrainingMangerLMS\Constants as Constants;
/**
 * Training LMS Event class
 *
 * This class provides encapsulation and functionality
 * for events associated with a course.
 *
 */
import('WP_DB_Object');

abstract class Event extends \WP_DB_Object {
protected static $fields = array(
	'description' => array( 'source' => 'post', 'wp_name' => 'post_content' ), 
	'start_datetime'    => array( 'source' => 'synthesized', 'default' => NULL, 'type' => 'DateTime',
						'synthesis' => array( 'create' => 'persistStartDateTime', 'persist' => 'persistStartDateTime', 
						'read' => 'readStartDateTime', 'delete' => 'deleteStartDateTime' ) ),
	'stop_datetime'    => array( 'source' => 'synthesized', 'default' => NULL, 'type' => 'DateTime',
						'synthesis' => array( 'create' => 'persistStopDateTime', 'persist' => 'persistStopDateTime', 
						'read' => 'readStopDateTime', 'delete' => 'deleteStopDateTime' ) ),
);
protected static function getFieldsDescriptor() {
	return array_replace(parent::getFieldsDescriptor(), self::$fields);
}
protected static function prefix( $key ) {
	return ttp_lms_prefix('event_' . $key);
}
protected static function getPostType() {
	return ttp_lms_post_prefix(Constants::EventPostType);
}

	public function getStartDateTime() { // public function \DateTime
		$startDateTime = $this->start_datetime;
		return is_null($startDateTime) ? null : clone $startDateTime; // or pass back immutable
	}
	public function hasPassed(\DateTime $datetime) {
		return $this->start_datetime < $datetime;
	}
	public function inProgress(\DateTime $datetime) {
		$startDateTime = $this->start_datetime;
		$stopDateTime = $this->stop_datetime;
		if (is_null($stopDateTime) || is_null($startDateTime)) return false;
		return ($datetime >= $startDateTime) && ($datetime <= $stopDateTime);
	}
	public function getStopDateTime() {
		$stopDateTime = $this->stop_datetime;
		return is_null($stopDateTime) ? null : clone $stopDateTime;
	}

	public function getDuration() {
		$stopDateTime = $this->stop_datetime;
		$startDateTime = $this->start_datetime;
		if (is_null($stopDateTime) || is_null($startDateTime)) return 0;
		$startDateTime = \DateTime::createFromFormat('Y-m-d H:i:s', $startDateTime->format('Y-m-d') . " 00:00:00");
		$stopDateTime = \DateTime::createFromFormat('Y-m-d H:i:s', $stopDateTime->format('Y-m-d') . " 00:00:00");
		return (($stopDateTime->getTimestamp() - $startDateTime->getTimestamp()) / (24 * 3600)) + 1;
	}

	protected static function persistStartDateTime($id, \DateTime $datetime = null) {
		if (is_null($datetime)) {
			static::deleteStartDateTime($id);
			return;
		}
		$datetime_utc = clone $datetime; $datetime_utc->setTimezone(new \DateTimeZone('UTC'));
		update_post_meta( $id, ttp_lms_prefix('event_start_datetime_utc'), $datetime_utc->format("Y-m-d H:i:s"));
		update_post_meta( $id, ttp_lms_prefix('event_start_datetime_tz'), $datetime->getTimezone()->getName());
	}
	protected static function readStartDateTime($id) {
		// make sure it is set
		$datetime_db = get_post_meta( $id, ttp_lms_prefix('event_start_datetime_utc'), false );
		if (count($datetime_db) == 0) return null;
		$datetime_db = $datetime_db[0];

		$timezone = new \DateTimeZone(get_post_meta( $id, ttp_lms_prefix('event_start_datetime_tz'), true ));
		$datetime_db = get_post_meta( $id, ttp_lms_prefix('event_start_datetime_utc'), true );
		$datetime_utc = \DateTime::createFromFormat("Y-m-d H:i:s", $datetime_db, new \DateTimeZone('UTC'));
		$datetime = clone $datetime_utc; $datetime->setTimezone($timezone);
		return $datetime;
	}
	protected static function deleteStartDateTime($id) {
		delete_user_meta( $id, ttp_lms_prefix('event_start_datetime_utc') );
		delete_user_meta( $id, ttp_lms_prefix('event_start_datetime_tz') );
	}

	protected static function persistStopDateTime($id, \DateTime $datetime = null) {
		if (is_null($datetime)) {
			static::deleteStopDateTime($id);
			return;
		}
		$datetime_utc = clone $datetime; $datetime_utc->setTimezone(new \DateTimeZone('UTC'));
		update_post_meta( $id, ttp_lms_prefix('event_stop_datetime_utc'), $datetime_utc->format("Y-m-d H:i:s"));
		update_post_meta( $id, ttp_lms_prefix('event_stop_datetime_tz'), $datetime->getTimezone()->getName());
	}
	protected static function readStopDateTime($id) {
		// make sure it is set
		$datetime_db = get_post_meta( $id, ttp_lms_prefix('event_stop_datetime_utc'), false );
		if (count($datetime_db) == 0) return null;
		$datetime_db = $datetime_db[0];

		$timezone = new \DateTimeZone(get_post_meta( $id, ttp_lms_prefix('event_stop_datetime_tz'), true ));
		$datetime_db = get_post_meta( $id, ttp_lms_prefix('event_stop_datetime_utc'), true );
		$datetime_utc = \DateTime::createFromFormat("Y-m-d H:i:s", $datetime_db, new \DateTimeZone('UTC'));
		$datetime = clone $datetime_utc; $datetime->setTimezone($timezone);
		return $datetime;
	}
	protected static function deleteStopDateTime($id) {
		delete_user_meta( $id, ttp_lms_prefix('event_stop_datetime_utc') );
		delete_user_meta( $id, ttp_lms_prefix('event_stop_datetime_tz') );
	}
}

?>
