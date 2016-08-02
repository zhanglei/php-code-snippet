<?php
include_once "engine.php";
class curl_multi_engine extends engine
{
	public function __contruct($urls)
	{
		$this->_urls = $urls;
	}

	public function add_url($url)
	{
		$this->_urls[$i] = $url;
	}

	public function exec() 
	{
		$running = null;
		$mh = curl_multi_init();
		$connections = array();
		foreach ($this->_urls as $i=>$url)
		{
		    $connections[$i]=curl_init($url);
		    curl_setopt($connections[$i], CURLOPT_RETURNTRANSFER, 1);
		    curl_multi_add_handle ($mh,$connections[$i]);
		}
		do
		{
            $mrc = curl_multi_exec($mh,$running);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);

		while ($running and $mrc == CURLM_OK)
		{
            if (curl_multi_select($mh) != -1)
            {
                do
                {
                    $mrc = curl_multi_exec($mh, $running);
                } while ($mrc == CURLM_CALL_MULTI_PERFORM);
            }
    	}
    	foreach ($this->_urls as $i=>$url)
    	{
    		$this->_contents[$i]=curl_multi_getcontent($connections[$i]);
			curl_close($connections[$i]);
		}
		unset($connections);
	}

	public function getContent($name)
	{
		return $this->_contents[$name];
	}
}