<?php
$email = 'cloud_1985+aaa@asdasd_.caca';
var_dump(preg_match("/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.[a-z]{1,4}$/",$email));