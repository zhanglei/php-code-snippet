<?php
function callback($callable) {
	return function ($a,$b) use ($callable) {
		return $callable($a,$b);
	};
}
$obj = callback(function($q,$p) { echo $q . ' ' . $p; });
$msg1 = "Hello";
$msg2 = "world";  
$obj($msg1,$msg2);