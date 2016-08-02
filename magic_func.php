<?php
class magic {
	$data = array();
	public function __construct() {
		echo '__construct';
	}
	public function __set() {
		
	}
	public function __get() {
		
	}
	public function __invoke() {
		echo '__invoke';
	}
	public function __destruct() {
		echo '__destruct';
	}
}
$magic = new magic();
$magic();