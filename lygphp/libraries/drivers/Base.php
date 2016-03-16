<?php

if (!defined('IN_IDEACMS')) exit();

abstract class Fn_base {
	
	/**
	 * 自动变量设置
	 */
	 public function __set($name, $value) {
	 	if (property_exists($this, $name)) $this->$name = $value;
	 }
	 
	 /**
	  * 自动变量获取
	  */
	 public function __get($name) {
	 	return isset($this->$name) ? $this->$name : false;
	 }
	 
	 /**
	  * 函数: __call()
	  */
	 public function __call($method, array $args) {
	 	echo 'Method:' . $method . '() is not exists in Class:' . get_class($this) . '!<br/>The args is:<br/>';
	 	foreach ($args as $value) {
	 		echo $value, '<br/>';
	 	}
	 }
	 
	 /**
	  * 输出类的实例化对象
	  */
	 public function __toString() {
	 	return (string)'This is ' . get_class($this) . ' Class!';
	 }
}