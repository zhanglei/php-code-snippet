<?php
namespace src\App\Controller;

use src\Base\Controller\Controller as BaseController;

class Controller extends BaseController {
	public function __construct() {
		parent::__construct();
		echo "init Controller in " . __FILE__ . "\n";
	}
}