<?php
include_once "engine.php";
class yar_engine extends engine
{
	public function __contruct($urls)
	{
		$this->_urls = $urls;
	}

	public function add_url($url)
	{
		$this->_urls[$i] = $url;
	}

	public function callback($ret, $callinfo)
	{
		echo json_encode($ret);
		var_export("\n");
	}
	
	public function exec() 
	{
		foreach($this->_urls as $url)
		{
			Yar_Concurrent_Client::call("http://localhost/yar_server.php", "run", array($url), "callback");
		}
		Yar_Concurrent_Client::loop();
	}

	public function getContent($name)
	{
		return $this->_contents[$name];
	}
}