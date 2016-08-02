<?php
function monkey($m,$n) {
	$monkeys = array();
	for($i=1;$i<=$n;$i++) {
		$monkeys[$i]['value'] = $i;
		if($i+1 > $n) {
			$monkeys[$i]['next'] = 1;
		} else {
			$monkeys[$i]['next'] = $i+1;
		}
		if($i==1) {
			$monkeys[$i]['prev'] = $n;
		} else {
			$monkeys[$i]['prev'] = $i-1;
		}
	}
	$rest = $n;
	$current = 1;
	$count = 0;
	while($rest > 1) {
		$count++;
		if($count%$m == 0) {
			$tmp = $current;
			$monkeys[$monkeys[$current]['prev']]['next'] = $monkeys[$current]['next'];
			$monkeys[$monkeys[$current]['next']]['prev'] = $monkeys[$current]['prev'];
			$current = $monkeys[$current]['next'];
			unset($monkeys[$tmp]);
			$rest--;
			$count = 0;
		} else {
			$current = $monkeys[$current]['next'];
		}
	}
	var_dump($monkeys);
}
monkey(2,3);