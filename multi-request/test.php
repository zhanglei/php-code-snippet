<?php
$client = new swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);
// swoole_async_dns_lookup('heroesofthestorm.com.cn', function($host, $ip) use ($client) {
// 	echo "$host -> $ip \n";
// });
$host='221.195.0.134';
$path='/';
$query=NULL;
$client->on("connect", function(swoole_client $cli) use ($host, $path, $query) {
	if(!isset($path))
   	{
		$path = '/';
   	}
	if(isset($query))
	{
		$path .= "?" . $query;
	}
	$header = "GET " . $path . " HTTP/1.1\r\n";
	$header .= "Host: " . $host . "\r\n";
	$header .= "User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.116 Safari/537.36\r\n";
	$header .= "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8\r\n";
	$header .= "Accept-Charset: utf-8\r\n";
	$header .= "Accept-Language: zh-CN,zh;q=0.8,en;q=0.6\r\n";
	$header .= "Connection: keep-alive\r\n";
	$header .= "\r\n";
	$cli->send($header);
});

$client->on("receive", function(swoole_client $cli, $data) {
	var_dump($data);
	$cli->close();
});

$client->on("error", function(swoole_client $cli) {
	$cli->close();
});

$client->on("close", function(swoole_client $cli) {
	echo "close";
});
$client->connect($host, 80, 0.5);