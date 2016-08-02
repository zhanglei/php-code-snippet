<?php
function msort(&$data,$begin,$end) {
	while($begin < $end) {
		$mid = intval(($begin+$end)/2);
		msort($data,$begin,$mid);
		msort($data,$mid+1,$end);
		return merge($data,$begin,$mid,$end);
	}
}

function merge(&$data,$begin,$mid,$end) {
	$temp_data = array();
	$i = $begin;
	$j = $mid+1;
	$k = 0;
    while($i<=$mid && $j<=$end) {
		if($data[$i] <= $data[$j]) {
			$temp_data[$k++] = $data[$i++];
		} else {
			$temp_data[$k++] = $data[$j++];
		}
	}
	while($i<=$mid) {
		$temp_data[$k++] = $data[$i++];
	}
	while($j<=$end) {
		$temp_data[$k++] = $data[$j++];
	}
	for($i=0;$i<$k;$i++) {
		$data[$begin+$i] = $temp_data[$i];
	}
}

$data = array(4,2,3,1,5);
msort($data,0,count($data)-1);
var_dump($data);