<?php

class A {
	static $fields = array('moose', 'cat', 'dog');
	static $name = "base";
	protected static function getFields($base) { return $base::getName(); }
	protected static function getField() { return self::$name; }
	protected static function getName() { return "base"; }
	public static function whereAmI() {
		$class = get_called_class();
		echo $class . "\n";
		echo get_parent_class($class);
	}
}

class B extends A {
	static $fields = array('pig', 'cow', 'chicken');
	static $name = "extended";
//	protected static function getFields() { return $fields; }
	protected static function getName() { return "extended"; }

	public function getParent() {
		$parent = get_parent_class();
		echo $parent::getName();
	}

	public function checkit() {
		echo print_r(static::getFields(get_class()), true);
		echo print_r(parent::getFields(get_parent_class()), true);
		$class = get_called_class();
		echo "|" . $class::getField() . "\n";
		$class = get_parent_class($class);
		echo "|" . $class::getField() . "\n";
	}
}

$b = new B();
$b->getParent();
$b::whereAmI();
echo $b->checkit();
?>
