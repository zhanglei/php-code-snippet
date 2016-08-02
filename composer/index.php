<?php
$loader = include 'vendor/autoload.php';

use App\Controller\Controller;
use App\Model\Model;

new Controller();
new Model();

var_dump($loader);exit;
