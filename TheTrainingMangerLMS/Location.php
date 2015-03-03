<?php
namespace TheTrainingMangerLMS;

/**
 * Training LMS Location class
 *
 * This class encapsulates fields and functionality
 * specific to a Location or Venue associated with a
 * Training Partners LMS Event.
 *
 **/
import('WP_DB_Object');

/*
	The class
*/
class Location extends \WP_DB_Object {

protected static $fields = array(
	'title' => array( 'source' => 'post', 'wp_name' => 'post_title', 'required' => true ),
	'address1' => array( 'source' => 'postmeta', 'default' => '' ),
	'address2' => array( 'source' => 'postmeta', 'default' => '' ),
	'city' => array( 'source' => 'postmeta', 'default' => '' ),
	'state' => array( 'source' => 'postmeta', 'default' => '' ),
	'zip' => array( 'source' => 'postmeta', 'default' => '' ),
	'country' => array( 'source' => 'postmeta', 'default' => '' ),
	'geo_code' => array( 'source' => 'postmeta', 'default' => '' )
);
protected static function getFieldsDescriptor() {
	return array_replace(parent::getFieldsDescriptor(), self::$fields);
}
protected static function prefix( $key ) {
	return ttp_lms_prefix('location_' . $key);
}
protected static function getPostType() {
	return ttp_lms_post_prefix(Constants::LocationPostType);
}

	public function getTitle() {
		return $this->title;
	}
	public function setTitle($title) {
		$this->title = $title;
	}

	public function getAddress1() {
		return $this->address1;
	}
	public function setAddress1($address1) {
		$this->address1 = $address1;
	}

	public function getAddress2() {
		return $this->address2;
	}
	public function setAddress2($address2) {
		$this->address2 = $address2;
	}

	public function getCity() {
		return $this->city;
	}
	public function setCity($city) {
		$this->city = $city;
	}

	public function getState() {
		return $this->state;
	}
	public function setState($state) {
		$this->state = $state;
	}

	public function getZip() {
		return $this->zip;
	}
	public function setZip($zip) {
		$this->zip = $zip;
	}

	public function getCountry() {
		return $this->country;
	}
	public function setCountry($country) {
		$this->country = $country;
	}

}
