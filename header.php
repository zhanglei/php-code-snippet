<?php
header("X-Sample-Test: foo");
header('Content-type: text/plain');
//������ͬ��ͷ��Ϣ���Ը���
header('Content-type: text/html');
var_dump(headers_list());