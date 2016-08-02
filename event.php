<?php
//EventSubscriber 批量添加Listener
interface EventSubscriber {
   public function getSubscribedEvents();
}
class CustomeEventSubscriber implements EventSubscriber {
	public function getSubscribedEvents() {
		return array(
			'respone'=>array(
				array('onResponse1',3),
				array('onResponse2',1),
				array('onResponse3',2),
				array(function(Event $event){ echo 'Closure executed!!!<br/>'; } ,2)
			)
		);
	}
	public function onResponse1(Event $event) {
		 $event->onResponse(__FUNCTION__);
	}
	public function onResponse2(Event $event) {
		 $event->onResponse(__FUNCTION__);
	}
	public function onResponse3(Event $event) {
		 $event->onResponse(__FUNCTION__);
	}
}
//Event 事件
class Event {
	private $popagationStopped = false;
	public function setPopagationStopped() {
		$this->popagationStopped = true;
	}
	public function isPopagationStopped() {
		return $this->popagationStopped;
	}
}
class CustomEvent extends Event {
	public function onResponse($function) {
		echo $function . ' Event executed!!!<br/>';
	}
}
//EventDispather  事件分发
class EventDispather {
	private $_listeners = array();
	private $_sorted_listeners = array();
	public function addListener($name,$listener,$priority=0) {
		$this->_listeners[$name][$priority][] = $listener;
	}
	public function dispather($name,Event $event = null) {
		if(null == $event) {
			$event = new Event();
		}
		if(!isset($this->_sorted_listeners[$name])) {
			$this->sortListeners($name);
		}
		foreach($this->_sorted_listeners[$name] as $listener) {
			if(is_callable($listener)) {
				call_user_func($listener,$event);
			} else if($listener[1] instanceof Closure) {
				call_user_func($listener[1],$event);
			} else {
				throw new Exception('Illegal listerner');
			}
			if($event->isPopagationStopped()) {
				break;	
			}
		}
	}
	public function addSubscriber(EventSubscriber $subscriber) {
		foreach($subscriber->getSubscribedEvents() as $name=>$params) {
			if(is_string($params)) {
				$this->addListener($name,array($subscriber,$params));
			} else if (is_string($params[0])) {
				$this->addListener($name,array($subscriber,$params[0]),isset($params[1]) ? $params[1] : 0);
			} else {
				foreach($params as $listener) {
					$this->addListener($name,array($subscriber,$listener[0]),isset($listener[1]) ? $listener[1] : 0);
				}
			}
		}
	}
	private function sortListeners($name) {
		$this->_sorted_listeners[$name] = array();
		if(isset($this->_listeners[$name])) {
			krsort($this->_listeners[$name]);
			$this->_sorted_listeners[$name] = call_user_func_array('array_merge', $this->_listeners[$name]);
		}
	}
}
//实例化事件
$event = new CustomEvent();
//阻止冒泡事件
//$event->setPopagationStopped();
//实例化事件提供者
$event_subscriber = new CustomeEventSubscriber();
//实例化事件分发器
$event_dispather = new EventDispather();
//添加事件提供者
$event_dispather->addSubscriber($event_subscriber);
//执行事件
$event_dispather->dispather('respone', $event);

