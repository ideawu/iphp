<?php
class Context
{
	private $__data = array();
	
	function as_array(){
		return $this->__data;
	}

	function __set($name, $value){
		$this->__data[$name] = $value;
		$this->$name = $value;
	}

	function __get($name){
		if (array_key_exists($name, $this->__data)) {
			return $this->__data[$name];
		}
		return null;
	}

	/**  PHP 5.1.0之后版本 */
	function __isset($name){
		return isset($this->__data[$name]);
	}

	/**  PHP 5.1.0之后版本 */
	function __unset($name){
		unset($this->__data[$name]);
	}
}
