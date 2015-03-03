<?php
namespace TheTrainingMangerLMS;

/**
 * Training LMS User class
 *
 * This class encapsulates fields and functionality
 * specific to Training LMS User.
 *
 **/
require_once "user_functions.php";

/*
	The class
*/
class User {
// Post id
protected $id;
// Fields descriptor
protected static $fields = array(
	'login'        => array( 'wp_name' => 'user_login', 'required' => true, 'source' => 'user' ),
	'password'     => array( 'wp_name' => 'user_pass', 'required' => true, 'source' => 'user' ),
	'display_name' => array( 'wp_name' => 'display_name',  'default' => NULL, 'source' => 'user' ),
	'nice_name'    => array( 'wp_name' => 'user_nicename', 'default' => NULL, 'source' => 'user' ),
	'email'        => array( 'wp_name' => 'user_email',    'default' => NULL, 'source' => 'user' ),
	'date_registered' => array( 'wp_name' => 'user_registered', 'default' => NULL, 'source' => 'user' ),
	'status'       => array( 'source' => 'usermeta', 'default' => 'active' ),
);
protected static function getFieldsDescriptor() {
	return self::$fields;
}
protected static function getFields() {
	return self::$fields;
}
protected static function isInstantiable() {
	return true;
}

// this will allow User's to have multiple "roles"
	protected static function prefix($key) { return ttp_lms_prefix($key); }
	protected static function getUserObjectType($user_id) {
		$pot = get_user_meta($user_id, static::prefix('type'), false);
		if (count($pot) == 0)
			throw new \RuntimeException("Unable to get user object type.");
		return $pot[0];
	}
	protected static function setUserObjectType($user_id) {
		if (add_user_meta($user_id, static::prefix('type'), str_replace("\\", "\\\\", get_called_class()), true) === FALSE)
			throw new \RuntimeException("Unable to add user object type.");
	}
	protected static function removeUserObjectType($user_id) {
		delete_user_meta($user_id, static::prefix('type'));
	}

protected $cache;
/*
	Constructor/instance methods
*/
	protected function __construct(\WP_User $wp_user) {
		$this->id = $wp_user->ID;
		$this->cache = array();
		// read in meta
		foreach(static::getFieldsDescriptor() as $name => $args) {
			if ($args['source'] == 'user') {
				$this->cache[$name] = $wp_user->$args['wp_name'];
			} elseif ($args['source'] == 'usermeta') {
				$usermeta = get_user_meta( $this->id, static::prefix($name), false ); // TODO: This should always return a value
				$this->cache[$name] = count($usermeta) != 0 ? $usermeta[0] : $args['default'];
			}
		}
	}

	public static function instance( $id ) {
		$wp_user = get_user_by( 'id', $id );
		if (!$wp_user) return NULL;
		$userObjectType = static::getUserObjectType($id);
		return new $userObjectType($wp_user);
	}
	/* This function is used for cloning and testing purposes */
	public static function create( $parameters, $options = array() ) {
		// MAYBE: required fiels, validate parameters
		$userdata = array();
		$fields = static::getFieldsDescriptor();
		foreach( $parameters as $name => $value ) {
			if (!array_key_exists($name, $fields))
				throw new \InvalidArgumentException("Unknown field name $name in parameter list.");
			// Create post first so only take post parameters
			if ($fields[$name]['source'] == 'user') {
				$userdata[$fields[$name]['wp_name']] = $value;
			}
		}
		// Change this to $GLOBALS['SKIP_SAVE_USER'] = true;
		remove_action('user_register', array(TheTrainingMangerLMS(), 'register_users'), 10);
		$user_id = wp_insert_user($userdata, true);
		// Change this to unset($GLOBALS['SKIP_SAVE_USER']);
		add_action( 'user_register',    array(TheTrainingMangerLMS(), 'register_users'), 10, 1 );
		if (is_wp_error($user_id))
			throw new \RuntimeException($user_id->get_error_message()); //TODO: WordPressException???

		// save user object type
		static::setUserObjectType($user_id);

		// now set usermeta fields; all names have been validated, so loop through $fields
		foreach( $fields as $name => $args) {
			if ($args['source'] != 'usermeta') continue;
			$value = array_key_exists($name, $parameters) ? $parameters[$name] : $args['default'];
			if (add_user_meta($user_id, static::prefix($name), $value, true) === FALSE)
				throw new \RuntimeException("Unable to add user meta field $name.");
		}
		return static::instance($user_id);
	}

	public static function promote(User $user, $parameters = array()) {
		// FIXME: Prevent call inside of User
		// if get_called_class == get_class
		$user_id = $user->ID();
		try {
			if (static::getUserObjectType($user_id))
				throw new \InvalidArgumentException("User has already been promoted.");
		} catch (\RuntimeException $re) {}

		// validate $parameters with $fields not getFieldsDescriptor
		$fields = static::$fields;
		foreach( $parameters as $name => $value ) {
			if (!array_key_exists($name, $fields))
				throw new \InvalidArgumentException("Unknown field name $name in parameter list.");
		}

		// save user object type
		static::setUserObjectType($user_id);

		foreach( $fields as $name => $args) {
			if ($args['source'] != 'usermeta') continue;
			$value = array_key_exists($name, $parameters) ? $parameters[$name] : $args['default'];
			if (add_user_meta($user_id, static::prefix($name), $value, true) === FALSE)
				throw new \RuntimeException("Unable to add user meta field $name.");
		}
		return static::instance($user_id);
	}

	protected function get_base( $key ) {
		$target = get_called_class();
		$base = $target;
		while (!in_array($key, array_keys($target::$fields))) {
			$target = get_parent_class($target);
			if ($target::isInstantiable())
				$base = $target;
		}
		return $base;
	}

	protected function get( $key ) {
		// make sure the key is one of ours
		$fields = static::getFieldsDescriptor();
		if (!array_key_exists($key, $fields))
			throw new InvalidArgumentException('Requested field ' . $key . ' is not a field in this class.');

		return $this->cache[$key];
	}

	protected function set( $key, $value ) {
		// make sure the key is one of ours
		$fields = static::getFieldsDescriptor();
		if (!array_key_exists($key, $fields))
			throw new InvalidArgumentException('Requested field ' . $key . ' is not a field in this class.');

		$field = $fields[$key];
		if ($field['source'] == 'usermeta') {
			$base = static::get_base();
			if (!update_user_meta($this->id, $base::prefix($key), $value))
				throw new RuntimeException('Update of user meta failed.');
		} else if ($field['source'] == 'user') {
			$result = wp_update_user(array( 'ID' => $this->id, $field['wp_name'] => $value ) );
			if (is_wp_error($result))
				throw new \RuntimeException(serialize($result));
		}
	}

// __set uses wp_update_user
	/**
		Accessor methods
	**/
	public function ID() {
		return $this->id;
	}

	static function addMetaFields( $user_id ) {
		// add user_meta fields with defaults
		foreach(self::$fields as $name => $args) {
			if ($args['source'] != 'usermeta') continue;
			add_user_meta( $user_id, ttp_lms_prefix($name), $args['default'], true);
		}
	}

}

?>
