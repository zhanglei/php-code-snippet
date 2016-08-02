<?php
/*
class A\B\C
{
}

$abc = new A\B\C();
*/
namespace A\B;

class Foo
{
	public function __construct() {
		echo __CLASS__ . "\n";
	}
}

new Foo();

new \A\B\Foo();