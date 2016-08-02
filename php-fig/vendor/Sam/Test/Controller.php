<?php
namespace vendor\Sam\Test;

class Controller extends BaseController {
	public function __construct() {
		parent::__construct();
		echo 'init ' . __CLASS__ . '<br/>';
	}
}