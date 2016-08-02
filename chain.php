<?php
class chain {
    public $head = NULL;
    public $current = NULL;
	public function __construct() {
		$this->head = $this->current = new node();
	}
	//添加 value
	public function add($data) {
		$node = new node($data);
		$this->addNode($node);
	}
	//添加 node
	public function addNode(node $node) {
		if(isset($this->current)) {
			$this->current->next = &$node;
			$this->current = $this->current->next;
		} else {
			$this->head->next = &$node;
			$this->current = $this->head->next;
		}
	}
	//获取 node
	public function getNode($key) {
		$this->current = $this->head;
		while(($this->current = $this->current->next) != null) {
			if($this->current->data == $key) {
				return $this->current;
			}
		}
		return NULL;
	}
	//删除 node
	public function delete($key) {
		$this->reset();
		do {
			if(isset($this->current->next) && $key == $this->current->next->data) {
				$this->current->next = $this->current->next->next;
			}
		} while(($this->current = $this->current->next) != null);
	}
	//重置指针
	public function reset() {
		$this->current = $this->head->next;
	}
	//检查交叉
	public function check(chain $chain) {
		$this->reset();
		$chain->reset();
		while($this->current && $chain->current) {
			if($this->current === $chain->current) {
				var_dump('Intersect Point is ' . $this->current->data);
				return ;
			}
		    $this->current = $this->current->next;
			$chain->current = $chain->current->next;
		}
		var_dump('Not Exists');
	}
}
class node {
	public $data = array();
	public $next = NULL;
	public function __construct($data = array()) {
		$this->data = $data;
		$this->next = NULL;
	}
}
//初始化
$chain = new chain();
//添加 node
$chain->add('6');
$chain->add('8');
$chain->add('5');
$chain->add('7');
//获取 node
$node = $chain->getNode('7');
var_dump($node);
//删除某项
$chain->delete('8');
var_dump($chain->head->next);
//检查交叉1
$__chain1 = new chain();
$__chain1->add('6');
$chain->check($__chain1);
//检查交叉2
$node = $chain->getNode('6');
$__chain2 = new chain();
$__chain2->addNode($node);
$chain->check($__chain2);