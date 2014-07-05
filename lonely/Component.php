<?php
namespace LonelyGallery;

/* base class for all lonely classes */
abstract class Component {
	
	protected $data = array();
	
	/*
	property overloading
	On setting/getting of a property the setter/getter method is called.
	If there is no such method, it will be stored to / fetched from the $data property.
	Getters/setters start with get/set followed by one upper case letter, then only lower case.
	Example:
			$this->ABContent = 123;
		will call the method setAbcontent() if defined or it is stored as $data['abcontent'].
	*/
	
	public function __isset($name) {
		$name = strtolower($name);
		$method = 'get'.ucfirst($name);
		return (method_exists($this, $method) || isset($this->data[$name]));
	}
	
	public function __get($name) {
		$name = strtolower($name);
		$method = 'get'.ucfirst($name);
		if (method_exists($this, $method)) {
			return call_user_func(array($this, $method));
		}
		return isset($this->data[$name]) ? $this->data[$name] : null;
	}
	
	public function __set($name, $value) {
		$name = strtolower($name);
		$method = 'set'.ucfirst($name);
		if (method_exists($this, $method)) {
			return call_user_func(array($this, $method), $value);
		}
		$this->data[$name] = $value;
	}
	
	public function __unset($name) {
		$name = strtolower($name);
		$method = 'unset'.ucfirst($name);
		if (method_exists($this, $method)) {
			return call_user_func(array($this, $method), $value);
		}
		if (isset($this->data[$name])) {
			unset($this->data[$name]);
		}
	}
}
?>