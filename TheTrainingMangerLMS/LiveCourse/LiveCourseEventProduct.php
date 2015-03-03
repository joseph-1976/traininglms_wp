<?php
namespace TheTrainingMangerLMS\LiveCourse;

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

class LiveCourseEventProduct extends \TheTrainingMangerLMS\Product {

protected static $fields = array(
	'event_id' => array( 'source' => 'postmeta', 'default' => '', 'required' => true ),
);

protected static function getFieldsDescriptor() {
	return array_replace(parent::getFieldsDescriptor(), self::$fields);
}

}

/*Utility or LiveCourseInstance::getAssociatedProduct(event_id); {
	search for post_type = 'product' where post_meta('event_id') = event_id;
	return NULL if not found; else LiveCourseEventProduct;
}*/
