<?php
class Operator {
    public function run($url, $params = array()) {
      $ret = $this->http_request($url,$params,2);
	  return $ret;
    }
    public function http_request( $url , $params = array(), $timeout = 2) {
        if ( !function_exists('curl_init') ) {
            exit('Need to open the curl extension');
        }
        $ci = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 1);
        curl_setopt($ci, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ci, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        curl_close ($ci);
        return $response;
    }
}

$server = new Yar_Server(new Operator());
$server->handle();