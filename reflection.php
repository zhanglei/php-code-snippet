<?php
class ClassOne {
	private function callClassOne() {
		print "In Class One";
	}
}
class ClassOneDelegator {
	private $targets;
	function __construct() {
		$this->target[] = new ClassOne();
	}
	function __call($name, $args) {
		foreach ($this->target as $obj) {
			$r = new ReflectionClass($obj);
			if ($method = $r->getMethod($name)) {
				if ($method->isPublic() && !$method->isAbstract()) {
					return $method->invoke($obj, $args);
				}
			}
		}
	}
}
$obj = new ClassOneDelegator();
$obj->callClassOne();