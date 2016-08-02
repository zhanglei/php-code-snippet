<?php
/**
* Flexihash – A simple consistent hashing implementation for PHP.
*
* The MIT License
*
* Copyright (c) 2008 Paul Annesley
*
* Permission is hereby granted, free of charge, to any person obtaining a copy
* of this software and associated documentation files (the "Software"), to deal
* in the Software without restriction, including without limitation the rights
* to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
* copies of the Software, and to permit persons to whom the Software is
* furnished to do so, subject to the following conditions:
*
* The above copyright notice and this permission notice shall be included in
* all copies or substantial portions of the Software.
* b
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
* IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
* FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
* AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
* LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
* OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
* THE SOFTWARE.
*
* @author Paul Annesley
* @link http://paul.annesley.cc/
* @copyright Paul Annesley, 2008
* @comment by MyZ (http://blog.csdn.net/mayongzhan/)
*/
/**
* A simple consistent hashing implementation with pluggable hash algorithms.
*
* @author Paul Annesley
* @package Flexihash
* @licence http://www.opensource.org/licenses/mit-license.php
*/
class Flexihash {
	/**
	* The nodes to hash each target to.
	*
	* @var int
	* @comment 节点数
	*/
	private $_nodes = array();
	/**
	* The node hasher.
	*
	* @var object Flexihash_Node_Hasher
	* @comment 节点hash方法
	*/
	private $_node_hasher = array();
	/**
	* The number of virtual nodes for each node.
	*
	* @var int
	* @comment 虚拟节点数,解决节点分布不均的问题
	*/
	private $_replicas = 64;
	/**
	* The hash algorithm, encapsulated in a Flexihash_Hasher implementation.
	* @var object Flexihash_Hasher
	* @comment 使用的hash方法 : md5,crc32
	*/
	private $_hasher = null;
	/**
	* Internal counter for current number of targets.
	* @var int
	* @comment 节点记数器
	*/
	private $_targetCount = 0;
	/**
	* Internal map of positions (hash outputs) to targets
	* @var array { position => target, … }
	* @comment 位置对应节点,用于lookup中根据位置确定要访问的节点
	*/
	private $_nodeToTarget = array();
	/**
	* Internal map of targets to lists of positions that target is hashed to.
	* @var array { target => [ position, position, ... ], … }
	* @comment 节点对应位置,用于删除节点
	*/
	private $_targetToNodes = array();
	/**
	* Constructor
	* @param object $hasher Flexihash_Hasher
	* @param int $replicas Amount of positions to hash each target to.
	* @comment 构造函数,确定要使用的hash方法和需拟节点数,虚拟节点数越多,分布越均匀,但程序的分布式运算越慢
	*/
	public function __construct(Flexihash_Hasher $hasher = null, Flexihash_NodeHasher $node_hasher = null, $nodes = null, $replicas = null) {
		$this->_hasher = $hasher ? $hasher : new Flexihash_Crc32Hasher();
		if (!empty($nodes)) {
			$this->_nodes = $nodes;
		}
		if (!empty($replicas)) {
			$this->_replicas = $replicas;
		}
		$this->_node_hasher = $node_hasher ? $node_hasher : new Flexihash_VirtualNodeHasher($this->_hasher,$this->_nodes,$this->_replicas);
		
	}
	/**
	* Add a target.
	* @param string $target
	* @chainable
	* @comment 添加节点,根据虚拟节点数,将节点分布到多个虚拟位置上
	*/
	public function addTarget($target) {
		if (isset($this->_targetToNodes[$target])) {
			throw new Flexihash_Exception("Target '$target' already exists.");
		}
		$nodes = $this->_node_hasher->getNode();
		$node = $this->lookupNode($target,$nodes);
		$this->_nodeToTarget[$node][] = $target; // lookup
		$this->_targetToNodes[$target] = $node; // remove
		$this->_targetCount++;
		return $this;
	}
	/**
	* Add a list of targets.
	* @param array $targets
	* @chainable
	*/
	public function addTargets($targets) {
		foreach ($targets as $target) {
			$this->addTarget($target);
		}
		return $this;
	}
	/**
	* Remove a target.
	* @param string $target
	* @chainable
	*/
	public function removeTarget($target) {
		if (!isset($this->_targetToNodes[$target])) {
			throw new Flexihash_Exception("Target '$target' does not exist.");
		}
		$node = $this->_targetToNodes[$target];
		foreach($this->_nodeToTarget[$node] as $key=>$val) {
			if($val == $target) {
				unset($this->_nodeToTarget[$node][$key]);
			}
		}
		unset($this->_targetToNodes[$target]);
		$this->_targetCount--;
		return $this;
	}
	/**
	* A list of all potential targets
	* @return array
	*/
	public function getAllTargets() {
		return array_keys($this->_targetToNodes);
	}
	
	public function addNode($node) {
		$this->_node_hasher->addNode($node);
		return $this;
	}
	
	public function addNodes($nodes) {
		foreach ($nodes as $node) {
			$this->_node_hasher->addNode($node);
		}
		return $this;
	}
	/**
	* Looks up the target for the given resource.
	* @param string $resource
	* @return string
	*/
	public function lookup($target) {
		if (empty($this->_targetToNodes[$target])) {
			throw new Flexihash_Exception('No targets exist');
		}
		return $this->_targetToNodes[$target];
	}
	/**
	*
	*/
	public function lookupNode($target,$nodes) {
		$node_hash = NULL;
		$nodes_hash = $nodes_hash_tmp = array_keys($nodes);
		$nodes_hash_count = count($nodes_hash_tmp);
		$targetPosition = $this->_hasher->hash($target);
		while($nodes_hash_count > 2) {
			$mid = floor($nodes_hash_count/2);
			if($targetPosition > $nodes_hash_tmp[$mid]) {
				$nodes_hash_tmp = array_slice($nodes_hash_tmp,$mid);
			} else if ($targetPosition < $nodes_hash_tmp[$mid]) {
				$nodes_hash_tmp = array_slice($nodes_hash_tmp,0,$mid);
			} else {
				$node_hash = $nodes_hash_tmp[$mid];
			}
			$nodes_hash_count = count($nodes_hash_tmp);
		}
		// if can't find exactly node_hash, find the nearest larger node_hash in nearest node_hashs
		if(!isset($node_hash)){
			foreach($nodes_hash_tmp as $val) {
				if($targetPosition<$val) {
					$node_hash = $val;
				}
			}
		}
		// if can't find the nearest larger node_hash in nearest node_hashs, find the nearest larger node_hash larger all nearest node_hashs 
		if(!isset($node_hash)){
			$last = array_pop($nodes_hash_tmp);
			if(($key = array_search($last,$nodes_hash)) !== false) {
				$key++;
				if(isset($nodes_hash[$key])) {
					$node_hash = $nodes_hash[$key];
				}				
			}
		}
		// if can't find the nearest larger node_hash larger all nearest node_hashs 
		if(!isset($node_hash)){
			$node_hash = array_pop($nodes_hash);
		}
		return $nodes[$node_hash];
	}
	public function __toString() {
		return sprintf('%s{targets:[%s]}', get_class($this), implode(',', $this->getAllTargets()));
	}
}
/**
* Hashes given values into a sortable fixed size address space.
*
* @author Paul Annesley
* @package Flexihash
* @licence http://www.opensource.org/licenses/mit-license.php
*/
interface Flexihash_Hasher {
	/**
	* Hashes the given string into a 32bit address space.
	*
	* Note that the output may be more than 32bits of raw data, for example
	* hexidecimal characters representing a 32bit value.
	*
	* The data must have 0xFFFFFFFF possible values, and be sortable by
	* PHP sort functions using SORT_REGULAR.
	*
	* @param string
	* @return mixed A sortable format with 0xFFFFFFFF possible values
	*/
	public function hash($string);
}
/**
* Uses CRC32 to hash a value into a signed 32bit int address space.
* Under 32bit PHP this (safely) overflows into negatives ints.
*
* @author Paul Annesley
* @package Flexihash
* @licence http://www.opensource.org/licenses/mit-license.php
*/
class Flexihash_Crc32Hasher implements Flexihash_Hasher {
	/* (non-phpdoc)
	* @see Flexihash_Hasher::hash()
	*/
	public function hash($string) {
		return crc32($string);
	}
}
/**
* Uses CRC32 to hash a value into a 32bit binary string data address space.
*
* @author Paul Annesley
* @package Flexihash
* @licence http://www.opensource.org/licenses/mit-license.php
*/
class Flexihash_Md5Hasher implements Flexihash_Hasher {
	/* (non-phpdoc)
	* @see Flexihash_Hasher::hash()
	*/
	public function hash($string) {
		return substr(md5($string), 0, 8); // 8 hexits = 32bit
		// 4 bytes of binary md5 data could also be used, but
		// performance seems to be the same.
	}
}
interface Flexihash_NodeHasher {
	public function addNode($node);
	public function removeNode($node);
	public function getNode();
}
class Flexihash_VirtualNodeHasher implements Flexihash_NodeHasher {
	/**
	* The number of virtual nodes for each node.
	*
	* @var int
	* @comment 虚拟节点数,解决节点分布不均的问题
	*/
	private $node_replicas = 64;
	private $nodes = array();
	private $_hasher = null;
	public function __construct(Flexihash_Hasher $hasher, $nodes = array(), $node_replicas = NULL) {
		$this->_hasher = $hasher ? $hasher : new Flexihash_Crc32Hasher();
		if (!empty($nodes)) {
			$this->_nodes = $nodes;
		}
		if (!empty($node_replicas)) {
			$this->_node_replicas = $node_replicas;
		}
	}
	public function addNode($node) {
		$this->_nodes[] = $node;
		return true;
	}
	public function removeNode($node) {
		if(false !== ($key = array_search($node,$this->_nodes))) {
			unset($this->_nodes[$key]);
		}
	}
	public function getNode() {
		$v_node = array();
		foreach($this->_nodes as $node) {
			for($i=1;$i<=$this->node_replicas;$i++) {
				$v_node[$this->_hasher->hash($node . '#' . $i)] = $node;
			}
		}
		ksort($v_node);
		return $v_node;
	}
}
/**
* An exception thrown by Flexihash.
*
* @author Paul Annesley
* @package Flexihash
* @licence http://www.opensource.org/licenses/mit-license.php
*/
class Flexihash_Exception extends Exception {
}

$hash = new Flexihash();
$hash->addNodes(array('202.168.14.240','202.168.14.241'));
$hash->addTargets(array('a'=>'a','b'=>'b','c'=>'c','d'=>'d','e'=>'e','f'=>'f','g'=>'g','h'=>'h','i'=>'i'));
echo $hash->lookup('a') . '|';
echo $hash->lookup('b') . '|';
echo $hash->lookup('c') . '|';
echo $hash->lookup('d') . '|';
echo $hash->lookup('e') . '|';
echo $hash->lookup('f') . '|';
echo $hash->lookup('g') . '|';
echo $hash->lookup('h') . '|';
echo $hash->lookup('i') . '|';
