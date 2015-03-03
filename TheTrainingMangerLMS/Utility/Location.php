<?php
namespace TheTrainingMangerLMS\Utility;

/**
 * Training LMS Location class
 *
 * This class provides encapsulation and functionality
 * for a simple location association.
 *
 */

class Location {
private static $fields = array( 'address1', 'address2', 'city', 'state', 'zip' );
public $address1 = '';
public $address2 = '';
public $city = '';
public $state = '';
public $zip = '';
//migrate to POO
	public function __construct( $parameters = array() ) {
		foreach( $parameters as $key => $value ) {
			if (!in_array($key, self::$fields))
				throw new \InvalidArgumentException("Unknown parameter {$key}.");
		}
		//$this->cache = array();
		foreach( self::$fields as $name ) { // all defaults are ''
			$this->$name = array_key_exists($name, $parameters) ? $parameters[$name] : '';
		}
	}
/*	public function __get( $key ) {
		if (!array_key_exists($key, self::$fields)) {
			trigger_error("Field $key does not exist for this class", E_USER_ERROR);
		}
		return $this->$key;
	}

	public function __set( $key, $value ) {
		if (!array_key_exists($key, self::$fields)) {
			trigger_error("Field $key does not exist for this class", E_USER_ERROR);
		}
		$this->$key = $value;
	}
*/
}

?>
