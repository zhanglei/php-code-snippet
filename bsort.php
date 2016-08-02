<?php
$data = array(4,3,5,7,1,2);
$len = count($data);
for($i=0;$i<$len;$i++) {
	for($j=$i+1;$j<$len;$j++) {
		if($data[$j]<=$data[$i]) {
			$tmp = $data[$i];
			$data[$i] = $data[$j];
			$data[$j] = $tmp; 
		}
	}
}
var_dump($data);
