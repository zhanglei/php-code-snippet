<?php
function reverse_r($str) {
   $nstr = '';
   for ($i=strlen($str)-1; $i>=0; $i--) {
       if(ord(substr($str,$i,1))>127) { #:w���Ǻ��־���ĩ��ǰ ��������������ַ�
            $i--;
            $nstr .= $str[$i] . $str[$i+1];
        } else { #���ֽ�
            $nstr .= $str[$i];
        }                  
   }
   return $nstr;
}
echo reverse_r("'Hello ��ã�");