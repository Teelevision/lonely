<?php
namespace LonelyGallery;

/* class to extend when developing a module */
abstract class Module {
	
	/* sets the LonelyGallery reference */
	function __construct() {
		$this->afterConstruct();
	}
	
	/* executed after __construct() */
	public function afterConstruct() {
		/* nothing */
	}
	
	/* returns settings for lonely */
	public function settings() {
		return array();
	}
	
	/* returns array of file classes to priority */
	public function fileClasses() {
		return array();
	}
	
	/* handle request */
	public function handleRequest(Request $request) {
		/* return true if further requests handling is allowed */
		/* return false if the request is handled */
		return true;
	}
}
?>