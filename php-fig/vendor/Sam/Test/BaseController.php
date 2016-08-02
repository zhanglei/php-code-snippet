<?php
namespace vendor\Sam\Test;

abstract class BaseController {
	public function __construct() {
		echo 'init ' . __CLASS__ . '<br/>';
	}
}