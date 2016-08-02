<?php
function quicksort(&$data,$left,$right) {
    if($left < $right) {
		$pivot = partition($data,$left,$right);
		quicksort($data,$left,$pivot-1);
		quicksort($data,$pivot+1,$right);
	}
	return $data;
}
function partition(&$data,$low,$high) {
	$pivot=$data[$low];
	while($low<$high) {
		while($low<$high && $data[$high]>=$pivot) {
			$high--;
		}
		$data[$low] = $data[$high];
		while($low<$high && $data[$low]<=$pivot) {
			$low++;
		}
		$data[$high] = $data[$low];
	}
	$data[$low] = $pivot;
	return $low;
}
$foo = array(3,4,2);
quicksort($foo,0,count($foo)-1);
print_r($foo);exit;

