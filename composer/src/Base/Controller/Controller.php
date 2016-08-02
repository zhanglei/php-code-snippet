<?php
namespace Base\Controller;

use Base\Controller\BaseController;

abstract class Controller extends BaseController {
	public function __construct() {
		parent::__construct();
		echo "init Controller in " . __FILE__ . "\n";
	}
}