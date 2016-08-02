<?php
function reverse_r($str) {
   $nstr = '';
   for ($i=strlen($str)-1; $i>=0; $i--) {
       if(ord(substr($str,$i,1))>127) { #:w如是汉字就自末向前 输出接连的两个字符
            $i--;
            $nstr .= $str[$i] . $str[$i+1];
        } else { #单字节
            $nstr .= $str[$i];
        }                  
   }
   return $nstr;
}
echo reverse_r("'Hello 你好！");