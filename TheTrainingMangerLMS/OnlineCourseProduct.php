<?php
namespace TheTrainingMangerLMS;

/**
 * Training LMS (WooCommerce) Course Product class
 *
 * This class encapsulates fields and functionality
 * specific to Training LMS Product
 * which is actually a wrapper for the WooCommerce
 * Product post type.
 *
 **/
import('TheTrainingMangerLMS.Product');
/*
	The class
*/

class OnlineCourseProduct extends Product {

protected static $fields = array(
	'course_id' => array( 'source' => 'postmeta', 'default' => '', 'required' => true ),
);

protected static function getFieldsDescriptor() {
	return array_replace(parent::getFieldsDescriptor(), self::$fields);
}

}
