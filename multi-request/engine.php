<?php
abstract class engine
{
	public $_urls = array();
	public $_contents = array();
	abstract public function add_url($url);
	abstract public function exec();
}