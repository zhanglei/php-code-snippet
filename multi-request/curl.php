<?php
include_once "engine.php";
class curl_engine extends engine
{
	public function __contruct($urls)
	{
		$this->_urls = $url;
	}

	public function add_url($url)
	{
		$this->_urls[] = $url;
	}

	public function exec() 
	{
		$ch = curl_init();
    	foreach ($this->_urls as $i => $url) {
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$this->_contents[$i] = curl_exec($ch);
		}
		curl_close($ch);
	}

	public function getContent($name)
	{
		return $this->_contents[$name];
	}
}