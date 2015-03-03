<?php
namespace TheTrainingMangerLMS\Utility;

//import('TheTrainingMangerLMS.Utility.Validation');

abstract class PostOptions {
protected $id;

abstract static protected function getDBOptionName();
abstract static protected function getFieldsDescriptor();

	private function __construct($id, $parameters) {
		$this->id = $id;
		$this->cache = array();
		foreach(static::getFieldsDescriptor() as $name => $args) {
			if(!array_key_exists($name, $parameters))
				throw new \InvalidArgumentException("Option {$name} missing from parameters.");
			// TODO: validate parameter
			$this->cache[$name] = $parameters[$name];
		}
	}
	static public function load($id) {
		// verify entry exists in post_meta
		$parameters = get_post_meta( $id, static::getDBOptionName(), false);
		if (count($parameters) == 0)
			throw new \InvalidArgumentException("Options for ID {$id} not found.");
		return new static($id, $parameters[0]);
	}
	static public function defaults() {
		$parameters = array();
		foreach (static::getFieldsDescriptor() as $name => $args) {
			$parameters[$name] = $args['default'];
		}
		return $parameters;
	}
	static public function create($id, $parameters) {
		// make sure $id doesn't already exist?
		if (count(get_post_meta($id, static::getDBOptionName(), false)) != 0)
			throw new \InvalidArgumentException("Options already exist for ID {$id}.");

		$parameters = array_replace(static::defaults(), $parameters);
		$options = new static($id, $parameters);
		$options->update();
		return $options;
	}
	public function update() {
		update_post_meta($this->id, static::getDBOptionName(), $this->cache);
	}

	/**
		Accessor methods
	**/
	public function ID() {
		return $this->id;
	}

	public function setOption($key, $value) {
		if (!array_key_exists($key, $this->cache))
			throw new \InvalidArgumentException("Key {$key} is not an option.");
		// TODO: validate value
		$this->cache[$key] = $value;
	}
	public function getOption($key) {
		if (!array_key_exists($key, $this->cache))
			throw new \InvalidArgumentException("Key {$key} is not an option.");
		return $this->cache[$key];
	}

}

?>