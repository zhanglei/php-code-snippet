<?php
namespace src\Base\Controller;

use src\Base\Controller\BaseController;

abstract class Controller extends BaseController {
	public function __construct() {
		parent::__construct();
		echo "init Controller in " . __FILE__ . "\n";
	}
}