<?php
namespace src\Base\Controller;

abstract class BaseController {
	public function __construct() {
		echo "init BaseController in " . __FILE__ . "\n";
	}
}