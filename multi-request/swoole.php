<?php
include_once "engine.php";
class swoole_engine extends engine
{
    public $request_count = 0;
    private $_table = NULL;
    public function __construct($urls)
    {
        $this->_urls = $urls;
    }

    public function add_url($url)
    {
        $this->_urls[] = $url;
    }

    public function exec() 
    {
        // 初始化表格
        $this->_initTable();
        // 发送请求
        $clients = array();
        foreach ($this->_urls as $key=>$url)
        {
            $this->send($key,$url);
        }
    }

    private function send($name, $url)
    {
        $parsed_url = parse_url($url);
        $host = $parsed_url['host'];
        if(isset($parsed_url['path'])) {
            $path = $parsed_url['path'];
        } else {
            $path = NULL;
        }
        if(isset($parsed_url['query'])) {
            $query = $parsed_url['query'];
        } else {
            $query = NULL;
        }
        // 连接服务器
        $client = new swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);
        $client->on("connect", function(swoole_client $cli) use ($name, $host, $path, $query) {
            $this->_table->set($name, array('content' => '', 'status' => 0));
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

        $client->on("receive", function(swoole_client $cli, $data) use ($name, $url) {
            $this->_table->set($name, array('content' => $data, 'status' => 1));
            $this->request_count++;

            $content = $this->_table->get($name);
            var_dump($content);
            if(count($this->_urls) == $this->request_count)
            {
                swoole_event_exit();
            }
            $cli->close();
        });

        $client->on("error", function(swoole_client $cli) {
            $cli->close();
        });

        $client->on("close", function(swoole_client $cli) {
            echo "close";
        });

        swoole_async_dns_lookup($host, function($host, $ip) use ($client) {
            $client->connect($ip, 80, 0.5);
        });
        return $client;
    }

    public function getContent($name)
    {
        $timeout = 10000000; //10s
        $interval = 200000; //200ms
        $content = NULL;
        do {
            usleep($interval);
            $timeout = $timeout - $interval;
            $content = $this->_table->get($name);
            var_dump($content);
        } while ($timeout>0 && empty($content['status']));

        return $content['data'];
    }

    private function getIP() 
    { 
        if (isset($_SERVER)) { 
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $realip = $_SERVER['HTTP_X_FORWARDED_FOR']; 
            } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) { 
                $realip = $_SERVER['HTTP_CLIENT_IP']; 
            } elseif (isset($_SERVER['REMOTE_ADDR'])) { 
                $realip = $_SERVER['REMOTE_ADDR'];
            } else {
                $realip = '192.168.199.119'; 
            }
        } else { 
            if (getenv("HTTP_X_FORWARDED_FOR")) { 
                $realip = getenv( "HTTP_X_FORWARDED_FOR"); 
            } elseif (getenv("HTTP_CLIENT_IP")) { 
                $realip = getenv("HTTP_CLIENT_IP"); 
            } else { 
                $realip = getenv("REMOTE_ADDR"); 
            } 
        } 
        return $realip; 
    }

    private function _initTable()
    {
        $table = new swoole_table(count($this->_urls));
        $table->column('content', swoole_table::TYPE_STRING, 10240);
        $table->column('status', swoole_table::TYPE_INT, 2);
        $table->create();

        $this->_table = $table;

        return TRUE;
    }
}