<?php
$url  = 'http://192.168.44.144:8010/lianjia-tp/key';
$path = '/root/phone_number.txt';

$fp = fopen($path, 'r+');

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
curl_setopt($ch, CURLOPT_FILE, $fp);
curl_setopt($ch, CURLOPT_UPLOAD, true);
curl_setopt($ch, CURLOPT_INFILESIZE, filesize($path));

$data = curl_exec($ch);

curl_close($ch);
fclose($fp);
