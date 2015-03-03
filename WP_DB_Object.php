<?php

// PHP has some erroneous warnings and notices for four of the class functions; will need to suppress them
abstract class WP_DB_Object implements Serializable {
// Post id
protected $id;
// Fields descriptor
protected static $fields = array();
protected static function getFieldsDescriptor() {
	return self::$fields;
}

protected $__options = array('cache_updates' => false);
protected $cache;
protected $updates_cache;

	protected static abstract function prefix( $key );
	protected static abstract function getPostType();

	protected static function getPostObjectType($post_id) {
		$pot = get_post_meta($post_id, static::prefix('type'), false);
		if (count($pot) == 0)
			throw new \RuntimeException("Unable to get post object type.");
		return $pot[0];
	}
	protected static function setPostObjectType($post_id) {
		if (add_post_meta($post_id, static::prefix('type'), str_replace("\\", "\\\\", get_called_class()), true) === FALSE)
			throw new \RuntimeException("Unable to add post object type.");
	}
	protected static function removePostObjectType($post_id) {
		delete_post_meta($post_id, static::prefix('type'));
	}

/*
	Constructor/instance methods
*/
	private function __construct(\WP_Post $wp_post) {
		$this->id = $wp_post->ID;
		$this->cache = array();
		// assemble cache for fields
		foreach(static::getFieldsDescriptor() as $name => $args) {
			if ($args['source'] == 'post') {
				$this->cache[$name] = $wp_post->$args['wp_name'];
			} elseif ($args['source'] == 'postmeta') {
				$this->cache[$name] = get_post_meta( $this->id, static::prefix($name), true );
			} elseif ($args['source'] == 'synthesized') {
				$this->cache[$name] = static::$args['synthesis']['read']($this->id);
			}
		}
	}
	public static function instance( $post_id, $options = array() ) {
		// TODO: Use a global Registry to store loaded instances
		// is an instance of this already loaded, then return it
		$wp_post = get_post( $post_id );
		if ( is_null($wp_post) || is_wp_error($wp_post) )
			throw new \InvalidArgumentException("Unable to retrieve post for ID {$post_id}.");
		if ($wp_post->post_type != static::getPostType())
			throw new \InvalidArgumentException("ID " . $post_id . " is not a " . static::getPostType() . ".");
		$objectType = static::getPostObjectType($post_id);
		return new $objectType($wp_post);
	}
	public static function create( $parameters, $options = array() ) {
		$post = array( 'post_type' => static::getPostType());
		$fields = static::getFieldsDescriptor();
		$diff = array_diff(array_keys($parameters), array_keys($fields));
		if (count($diff))
			throw new \InvalidArgumentException("Unknown field name(s) " . implode(',', $diff) . " in parameter list.");
		// TODO: merge defaults here, maybe look for required
		$required = array_filter($fields, function($args) { return array_key_exists('required', $args) && $args['required'] === true; });
		$missing = array_diff(array_keys($required), array_keys($parameters));
		if (count($missing))
			throw new \InvalidArgumentException("Missing values for required field(s) " . implode(',', $missing) . ".");
		foreach( $parameters as $name => $value ) {
			// Create post first so only take post parameters
			if ($fields[$name]['source'] == 'post') {
				$post[$fields[$name]['wp_name']] = $value;
			}
		}
		// For consistency in post creation
	 	$datetime = new \DateTime();
		$post['post_date'] = $datetime->format("Y-m-d H:i:s");
		$datetime->setTimezone(new \DateTimeZone('UTC'));
		$post['post_date_gmt'] = $datetime->format("Y-m-d H:i:s");

		$post_id = wp_insert_post($post, true);
		if (is_wp_error($post_id))
			throw new \RuntimeException(serialize($post_id));

		// save post object type
		static::setPostObjectType($post_id);

		// now set postmeta fields; all names have been validated, so loop through $fields
		foreach( $fields as $name => $args) {
			if ($args['source'] == 'post') continue;
			elseif ($args['source'] == 'postmeta') {
				$value = array_key_exists($name, $parameters) ? $parameters[$name] : $args['default'];
				if (add_post_meta($post_id, static::prefix($name), self::to_string($value), true) === FALSE)
					throw new \RuntimeException("Unable to add post meta field $name.");
			} elseif ($args['source'] == 'synthesized') {
				static::$args['synthesis']['create']($post_id, array_key_exists($name, $parameters) ? $parameters[$name] : $args['default']);
			}
		}

		// unlike WP, we want generate actions after the post AND postmeta have been saved
		// use WP do action infrastructure for now
		$object = static::instance($post_id, $options);
		do_action('create_object_' . static::getPostObjectType($post_id), $object);
		return $object;
	}
	/* TODO: public static function duplicate(WP_DB_Object $object, $options = array() ) {
	// cache is available for a Course that is passed in
	// need to decide if clone should be static, use course_id, otherwise $this
	// $course = static::instance( $course_id )
	// return static::create($object->cache, $options)
	}*/
	// TODO: public static function delete($course_id) or public function delete();
	// delete will be called in the UI, so we also need a function to delete post_meta, similar to what we have with User
	public static function delete($post_id) {
		// validate post_id and posttype
		$wp_post = get_post( $post_id );
		if ( is_null($wp_post) || is_wp_error($wp_post) )
			throw new \InvalidArgumentException("Unable to retrieve post for ID {$post_id}.");
		if ($wp_post->post_type != static::getPostType())
			throw new \InvalidArgumentException("ID " . $post_id . " is not a " . static::getPostType() . ".");
		$objectType = static::getPostObjectType($post_id);
		$objectType::__delete($post_id);
	}
	protected static function __delete( $post_id ) {
		// delete post_meta fields
		$fields = static::getFieldsDescriptor();
		foreach( $fields as $name => $args) {
			if ($args['source'] == 'post') continue;
			elseif ($args['source'] == 'postmeta') {
				if (delete_post_meta($post_id, static::prefix($name)) === FALSE)
					throw new \RuntimeException("Unable to delete post meta field $name.");
			} elseif ($args['source'] == 'synthesized') {
				static::$args['synthesis']['remove']($post_id);
			}
		}

		static::removePostObjectType($post_id);

		// delete post
		if (wp_delete_post($post_id, true) === FALSE)
			throw new \RuntimeException("Unable to delete post of ID {$post_id}.");
	}

	public function update($parameters = array()) {
		if (count(array_keys($parameters)) == 0) {
			if (!$this->__options['cache_updates'] || (count(array_keys($this->updates_cache)) == 0)) return;
			return $this->_update();
		} else {
			if ($this->__options['cache_updates'] && (count(array_keys($this->updates_cache)) != 0))
				throw new \InvalidArgumentException("Pending cache updates must be flushed before parameterized updates.");
		}

		// verify parameters names
		$fields = static::getFieldsDescriptor();
		$diff = array_diff(array_keys($parameters), array_keys($fields));
		if (count($diff))
			throw new \InvalidArgumentException("Unknown field name(s) " . implode(',', $diff) . " in parameter list.");

		$cache_updates_old = $this->__options['cache_updates']; $this->__options['cache_updates'] = true;
		//$vbe = new \ValidationBundleException();
		foreach( $parameters as $name => $value ) {
		//	try {
				$this->$name = $value;
		//	} catch (\ValidationException $ve) {
		//		$vbe->add($ve);
		//	}
		}
		/*if ($vbe->hasExceptions()) {
			// discard updates
			$this->updates_cache = array();
			$this->__options['cache_updates'] = $cache_updates_old;
			throw $vbe;
		}*/
		$this->__update();
		$this->__options['cache_updates'] = $cache_updates_old;
	}
	protected function __update() {
		foreach($this->updates_cache as $name => $value) {
			$this->persist( $name, $value );
		}
		$this->updates_cache = array();
	}

	public function __get( $key ) {
		if (!array_key_exists($key, $this->cache)) {
			trigger_error("Field $key does not exist for this class", E_USER_ERROR);
		}
		// if ($this->options['cache'] == false) return get_post_meta( $this->id, ttp_lms_prefix('lesson' . $key), true) else ...
		return $this->cache[$key];
	}

	public function __set( $key, $value ) {
		if (!array_key_exists($key, $this->cache)) { // use descriptor and some fields may only be accesible through class methods
			trigger_error("Field $key does not exist for this class", E_USER_ERROR);
		}

		// To prevent false failures, make sure the values are different, otherwise we're done
		if ($this->cache[$key] == $value) return;
		// TODO: validate

		if ($this->__options['cache_updates']) {
			$this->updates_cache = $this->updates_cache ? $this->updates_cache : array();
			$this->updates_cache[$key] = $value;
			return;
		}

		$this->persist( $key, $value );
	}

	private function persist( $key, $value ) {
		$this->cache[$key] = $value;

		$field = static::getFieldsDescriptor()[$key];
		if ($field['source'] == 'postmeta') {
			if (!update_post_meta( $this->id, static::prefix($key), self::to_string($value) ))
				throw new \RuntimeException("Update of post meta failed.");
		} elseif ($field['source'] == 'post') {
			$result = wp_update_post( array( 'ID' => $this->id, $field['wp_name'] => self::to_string($value) ), true );
			if (is_wp_error($result))
				throw new \RuntimeException(serialize($result));
		} elseif ($field['source'] == 'synthesized') {
			static::$field['synthesis']['persist']($this->id, $value);
		}
		do_action('update_object_' . static::getPostObjectType($this->id), $this, array( $key => $value ));
	}

	private static function to_string($value) {
		if (is_numeric($value)) {
			return (string)$value;
		} elseif (is_bool($value)) {
			return $value ? 'true' : 'false';
		} elseif (is_string($value)) {
			return str_replace("\\", "\\\\", $value);
		}
		return $value;
	}

	private static function recurse($value) {
			if (is_object($value)) {
				if (is_a($value, __CLASS__)) {
					$value = $value->serialize();
				} else {
					$value = serialize($value);
				}
			} elseif (is_array($value)) {
				foreach($value as $key => $item) {
					$value[$key] = self::recurse($item);
				}
			}
			return $value;
	}
	public function serialize() {
		$serial = array('type' => static::getPostObjectType($this->id), 'ID' => $this->id);
//		?{name:, type: value:}? or name => {type: , value:}
		foreach(static::getFieldsDescriptor() as $name => $args) {
			$serial[$name] = self::recurse($this->cache[$name]);
		}
		return json_encode($serial);
	}
	public function unserialize($serialized) {
		// can use this for update($parameters) instead
		/*(json_decode($serialized);
		get type and ID, remove from decoded
		load type by ID
		update(remaining)*/
	}

/* Needs 'restrict'ions on 'get' and 'set'
	public function __call($method, $args) {
		$prefix = substr($method, 0, 3);
		if (($prefix == 'get') || ($prefix == 'set')) {
			$key = ltrim(strtolower(preg_replace('/[A-Z]/', '_$0', substr($method, 3))), '_');
			if (array_key_exists($key, $this->cache)) {
				if ($prefix == 'get') {
					// there should be no arguments to get
					if (count($args))
						trigger_error("Method {$method} takes no parameters; " . count($args) . " given.", E_USER_ERROR);
					return $this->$key;
				} else {
					// there should only be one argument to set
					if (count($args) != 1)
						trigger_error("Method {$method} takes one parameter; " . count($args) . " given.", E_USER_ERROR);
					// TODO: check type, NULL, validation chain here
					$this->$key = $args[0];
				}
				return;
			}
		}
		trigger_error("Class " . get_class($this) . " has no method {$method}.", E_USER_ERROR);
	}*/

	/**
		Accessor methods
	**/
	public function ID() {
		return $this->id;
	}
}

?>
