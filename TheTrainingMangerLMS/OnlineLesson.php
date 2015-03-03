<?php
namespace TheTrainingMangerLMS;

/**
 * Training LMS base Course class
 *
 * This class encapsulates fields and functionality
 * specific to Training LMS Course.
 *
 **/
import('TheTrainingMangerLMS.Lesson');

/*
	The class
*/
class OnlineLesson extends Lesson {
protected static $fields = array(
);
protected static function getFieldsDescriptor() {
	return array_replace(parent::getFieldsDescriptor(), self::$fields);
}

}

?>
