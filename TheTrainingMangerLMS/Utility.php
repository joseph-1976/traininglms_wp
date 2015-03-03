<?php
namespace TheTrainingMangerLMS;

/**
 * Training LMS Utility class
 *
 * This class provides utility functions
 *
 **/

class Utility {
	public static $PERIOD_UNITS = array('SECOND', 'MINUTE', 'HOUR', 'DAY', 'MONTH', 'YEAR');
	public static function isValidPeriod($unit) {
		return in_array($unit, self::$PERIOD_UNITS);
	}
	public static function getExpirationTimeStamp($unit, $interval) {
		if (!self::isValidPeriod($unit))
			throw new \InvalidArgumentException("The Unit provided is not valid.");
		global $wpdb;
		return $wpdb->get_var( $wpdb->prepare( "SELECT TIMESTAMPADD(" . $unit . ", %d, NOW())", $interval ) );
	}

	public static function getIDWhereArrayFieldHasID($key, $id) {
		global $wpdb;
		return $wpdb->get_var($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = %s AND meta_value REGEXP '^a:[[:digit:]]+:\{(i:[[:digit:]]+;(s:[[:digit:]]+:\"[[:digit:]]+\"|i:[[:digit:]]);)*(i:[[:digit:]]+;(s:[[:digit:]]+:\"%d\"|i:%d);){1}(i:[[:digit:]]+;(s:[[:digit:]]+:\"[[:digit:]]+\"|i:[[:digit:]]);)*}$'", $key, $id, $id));
	}

	public static function getAssociatedProduct( $key, $ID ) {
		global $wpdb;
		$query = $wpdb->prepare(
			"SELECT ID FROM $wpdb->posts p 
			JOIN ($wpdb->postmeta pm) ON (p.ID = pm.post_id)
			WHERE p.post_type = %s AND pm.meta_key = %s AND pm.meta_value = %s",
			Constants::WooCommerceProductPostType, $key, $ID);
		$product_id = $wpdb->get_var($query);
		if (is_null($product_id)) return NULL;
		return Product::instance($product_id);
	}

	public static function returnIDIfHasAssociatedProduct( $key, $IDs ) {
		global $wpdb;
		return $wpdb->get_var($wpdb->prepare(
			"SELECT IFNULL(group_concat(pm.meta_value SEPARATOR ','), '') FROM $wpdb->posts p 
			JOIN ($wpdb->postmeta pm) ON (p.ID = pm.post_id)
			WHERE p.post_type = %s AND pm.meta_key = %s AND FIND_IN_SET(pm.meta_value, %s) GROUP BY pm.meta_value",
			Constants::WooCommerceProductPostType, $key, join(',',$IDs)));
	}

	public static function getFeaturedCoursesList( $filter = array( 'type' => 'all') ) {
		// validate $filter
		$type = $filter['type'];
		if (!in_array($type, array('all', 'live', 'online')))
			throw new \InvalidArgumentException("Invalid filter options for type; expecting one of 'all', 'online' or 'live'.");

		if ($type == 'all') $type = 'TheTrainingMangerLMS\OnlineCourse,TheTrainingMangerLMS\LiveCourse';
		elseif ($type == 'online') $type = 'TheTrainingMangerLMS\OnlineCourse';
		elseif ($type == 'live') $type = 'TheTrainingMangerLMS\LiveCourse';

		global $wpdb;
		$query = $wpdb->prepare(
			"SELECT IFNULL(GROUP_CONCAT(pt.ID SEPARATOR ','), '') AS list FROM $wpdb->posts pt
			JOIN ($wpdb->postmeta ct, $wpdb->postmeta ft) ON (pt.ID = ct.post_id AND pt.ID = ft.post_id)
			WHERE pt.post_type = %s AND ct.meta_key = %s AND FIND_IN_SET(ct.meta_value, %s)
			AND ft.meta_key = %s and ft.meta_value = 'true'",
			ttp_lms_post_prefix(Constants::CoursePostType), ttp_lms_prefix('course_type'), $type, ttp_lms_prefix('course_featured')
		);
		$list = $wpdb->get_var($query);
		return $list == '' ? array() : explode(',', $list);
	}

	public static function getAllOnlineCoursesList() {
		global $wpdb;
		$list = $wpdb->get_var($wpdb->prepare(
			"SELECT IFNULL(group_concat(ID SEPARATOR ','), '') AS list FROM $wpdb->posts pt
			JOIN ($wpdb->postmeta ct) ON (pt.ID = ct.post_id)
			WHERE pt.post_type = %s AND ct.meta_key = %s 
			AND ct.meta_value = 'TheTrainingMangerLMS\\\\OnlineCourse'",
			ttp_lms_post_prefix(Constants::CoursePostType), ttp_lms_prefix('course_type'))
		);
		return $list == '' ? array() : explode(',', $list);
	}

	public static function getAllLiveCoursesList() {
		global $wpdb;
		$list = $wpdb->get_var($wpdb->prepare(
			"SELECT IFNULL(group_concat(ID SEPARATOR ','), '') AS list FROM $wpdb->posts pt
			JOIN ($wpdb->postmeta ct) ON (pt.ID = ct.post_id)
			WHERE pt.post_type = %s AND ct.meta_key = %s 
			AND ct.meta_value = 'TheTrainingMangerLMS\\\\LiveCourse'",
			ttp_lms_post_prefix(Constants::CoursePostType), ttp_lms_prefix('course_type'))
		);
		return $list == '' ? array() : explode(',', $list);
	}

}

?>
