<?php
/*
class A\B\C
{
}

$abc = new A\B\C();
*/
namespace C\D;

class Foo
{
	public function __construct() {
		echo __CLASS__ . "\n";
	}
}

new Foo();