<?php
namespace TheTrainingMangerLMS;

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

import('WP_DB_Object');
import('TheTrainingMangerLMS.Trainer');
require_once('trainingcompany_functions.php');

class TrainingCompany extends \WP_DB_Object {

protected static $fields = array(
	'name' => array( 'source' => 'post', 'wp_name' => 'post_title', 'required' => true ),
	'trainers' => array( 'source' => 'postmeta', 'default' => array() )
);

protected static function getFieldsDescriptor() {
	return array_replace(parent::getFieldsDescriptor(), self::$fields);
}
protected static function prefix( $key ) {
	return ttp_lms_prefix('tc_' . $key);
}
protected static function getPostType() {
	return ttp_lms_post_prefix(Constants::TrainingCompanyPostType);
}

	public function getTrainers() {
		$trainers = array();
		foreach ( $this->trainers as $trainer ) {
			array_push($trainers, \TheTrainingMangerLMS\Trainer::instance($trainer));
		}
		return $trainers;
	}

	public function addTrainer(Trainer $trainer, $role) {
		// ignore role for now
		$trainers = $this->trainers;
		if (in_array($trainer->ID(), $trainers))
			throw new \InvalidArgumentException("Trainer already added.");
		array_push($trainers, $trainer->ID());
		$this->trainers = $trainers;
	/*how to save trainer and role
	separate table
	training company, trainer id, role*/
	}
	
}

?>
