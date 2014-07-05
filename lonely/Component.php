<?php
/*
##########################
### Lonely PHP Gallery ###
##########################
###     Component      ###
##########################
This file is part of the the Lonely Gallery.

### Version ###

1.1.0 dev
date: 2014-07-05

### License & Requirements & More ###

Copyright (c) 2014 Marius 'Teelevision' Neugebauer.
See LICENSE.txt, README.txt
and https://github.com/Teelevision/lonely

### Description ###

The Component is a basic class that allows property overloading.

On setting/getting of a property the setter/getter method is called.
If there is no such method, it will be stored to / fetched from the
$_data property.
Getters/setters start with get/set followed by one upper case letter,
then only lower case.
Example:
		$this->ABContent = 123;
	will call the method setAbcontent() if defined or it is stored as
	$_data['abcontent'].
*/

namespace LonelyGallery;

/* base class for all lonely classes */
abstract class Component {
	
	/* storage for properties */
	private $_data = array();
	
	/* called when isset() is called on a not defined property */
	public function __isset($name) {
		$name = strtolower($name);
		$method = 'get'.ucfirst($name);
		/* check if there is a method or a key in the storage */
		return (method_exists($this, $method) || isset($this->_data[$name]));
	}
	
	/* called when a not defined property is needed */
	public function __get($name) {
		$name = strtolower($name);
		$method = 'get'.ucfirst($name);
		/* call getter method if one exists */
		if (method_exists($this, $method)) {
			return call_user_func(array($this, $method));
		}
		/* return value from storage or null */
		return isset($this->_data[$name]) ? $this->_data[$name] : null;
	}
	
	/* called when a not defined property is set */
	public function __set($name, $value) {
		$name = strtolower($name);
		$method = 'set'.ucfirst($name);
		/* call setter method if one exists */
		if (method_exists($this, $method)) {
			return call_user_func(array($this, $method), $value);
		}
		/* into storage */
		$this->_data[$name] = $value;
	}
	
	/* called when unset() is called on a not defined property */
	public function __unset($name) {
		$name = strtolower($name);
		$method = 'unset'.ucfirst($name);
		/* call unset method if one exists */
		if (method_exists($this, $method)) {
			return call_user_func(array($this, $method), $value);
		}
		/* unset from storage */
		if (isset($this->_data[$name])) {
			unset($this->_data[$name]);
		}
	}
}
?>