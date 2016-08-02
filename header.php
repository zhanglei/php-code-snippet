<?php
header("X-Sample-Test: foo");
header('Content-type: text/plain');
//设置相同的头信息可以覆盖
header('Content-type: text/html');
var_dump(headers_list());