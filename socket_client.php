<?php
//set_time_limit(0);
$host = "192.168.1.121";
$port = 80;

$header = "GET / HTTP/1.1\r\n";
$header .= "Host: 192.168.1.121\r\n";
$header .= "User-Agent: PHP\r\n";
$header .= "Connection: keep-alive\r\n";
$header .= "Accept: text/html,application/xhtml+xml,application/xml;\r\n";
//$header .= "Accept-Charset: utf-8\r\n";
//$header .= "Accept-Encoding: gzip, deflate\r\n";
$header .= "Cache-Control: max-age=3600\r\n";
$header .= "Expire: " . gmdate ("D, d M Y H:i:s", time() + 3600) . " GMT\r\n\r\n";

if (($handle = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false) {
    echo "socket_create() failed: " . socket_strerror(socket_last_error()) . "\r\n";
}
if(socket_connect($handle, $host, $port) === false) {
	echo "socket_connect() failed: " . socket_strerror(socket_last_error($handle)) . "\r\n";
}
socket_write($handle, $header, strlen($header));
if(false !== ($bytes = socket_recv($handle, $buffer, 2048, MSG_WAITALL))) {
	echo $buffer;
}
socket_close($handle);
