<?php
namespace App\Model;

use Base\Model\BaseModel;

class Model extends BaseModel {
	public function __construct() {
		parent::__construct();
		echo "init Model in " . __FILE__ . "\n";
	}
}