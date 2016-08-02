<?php
include "curl.php";
include "curl_multi.php";
include "swoole.php";

class benchmark
{
	private $_engine = array();
	private $_start_time = NULL;
	private $_end_time = NULL;
	public function __construct($engine, $urls = NULL)
	{
		if(is_array($engine)) {
			if(isset($urls)) {
				foreach($engine as $i=>$e) {
					$engine[$i]->_urls = $urls;
				}
			}
			$this->_engine = $engine;
		} else if($engine instanceof engine) {
			if(isset($urls)) {
				$engine->_urls = $urls;
			}
			$this->_engine[] = $engine;
		} else {
			exit("engine type error");
		}
	}

	public function add_url($url)
	{
		foreach($this->_engine as $i=>$e) {
			$this->_engine[$i]->_urls[] = $url;
		}
	}

	public function exec()
	{
		$output = '';
		foreach($this->_engine as $i=>$e) {
			$this->_start_time = microtime(true);
			$e->exec();
		}
	}

	public function getContent($name)
	{
		$output = '';
		foreach($this->_engine as $i=>$e) {
			echo "=====================================\n";
			echo "content of $i:\n\n";
			echo $e->getContent($name) . "\n\n";
			$this->_end_time = microtime(true);
			echo $this->output($i, $e);
			echo "=====================================\n\n";
		}
	}

	private function output($name, $engine)
	{
		if($this->_start_time && $this->_end_time) {
			return $name . ': ' . count($engine->_urls) . ' requests totally spend ' . ($this->_end_time - $this->_start_time) . ' sec' . "\n";
		}
	}
}

// $urls = array(
// 	'http://i2.api.weibo.com/2/users/show.json?source=908033280&uid=1733025752',
// 	'http://i2.api.weibo.com/2/users/counts.json?source=908033280&uid=1733025752',
// 	'http://i2.api.weibo.com/2/statuses/user_timeline.json?source=908033280&uid=1733025752',
// 	'http://i2.api.weibo.com/2/friendships/friends.json?source=908033280&uid=1733025752',
// );
$urls = array(
	'http://www.heroesofthestorm.com.cn/landing',
	'http://www.cnbeta.com',
	//'http://www.hao123.com'
);
//$engines['curl'] = new curl_engine();
//$engines['curl_multi'] = new curl_multi_engine();
$engines['swoole'] = new swoole_engine();
$b = new benchmark($engines, $urls);
echo $b->exec();
// $b->getContent(0);
exit;
