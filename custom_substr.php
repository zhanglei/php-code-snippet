<?php
function custom_substr($str,$start,$len,$charset='UTF8') { 
	$tmp_str = '';
	$n=0;
	$i = $start;
	while($n<$len) {
		$tmp_substr = ord(substr($str,$i,1));
		switch($charset) {
			case 'GBK':
				if($tmp_substr>127) { // 1XXXXXXX 01XXXXXX
					$tmp_str .= substr($str,$i,2);
					$i = $i + 2;
				} else { //0XXXXXXX
					$tmp_str .= substr($str,$i,1);
					$i++;
				}
			;
			case 'UTF8':
				if ($tmp_substr>=224) { //1110XXXX XXXXXXXX XXXXXXXX
					$tmp_str .= substr($str,$i,3);
					$i = $i + 3;
				} else if($tmp_substr>=192) { //110XXXXX XXXXXXXX
					$tmp_str .= substr($str,$i,2);
					$i = $i + 2;
				} else { //0XXXXXXX
					$tmp_str .= substr($str,$i,1);
					$i++;
				}
			;
		}
		$n++;
	}
	return $tmp_str;
}
echo custom_substr("你好,世界!",0,3);