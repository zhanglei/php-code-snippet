<?php
//ПГЩўзг
function cut($m) {
	if($m<2) {
		return 0;
	} else {
		$count = 0;
		if($m%2) {
			$count += cut(($m-1)/2)+2;
		} else {
			$count += cut($m/2)+1;
		}
		return $count;
	}
}
echo cut(19);