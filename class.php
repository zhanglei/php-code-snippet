<?php
class foo {
	function __construct() {
		echo 'construct<br/>';
	}
	function __destruct() {
		echo 'destruct<br/>';
	}
	function __call($name,$arguments) {
	var_dump($arguments);
		if(isset($this->$name)) {
			return $this->$name;
		}
	}
	function call($arg1,$arg2) {
	}
}
$a = new foo();
$b = &$a;
$c = $b;
$c->scall(array(12312),1212);