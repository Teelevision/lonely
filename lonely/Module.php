<?php
/*
##########################
### Lonely PHP Gallery ###
##########################
###       Module       ###
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

This is the abstract base class for modules.
*/

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
	
	/* returns an array with config-relative web paths to ResourceFile instances */
	public function resources($forceAll = false) {
		return array();
	}
	
	/* config files */
	public function configAction(\LonelyGallery\Request $request) {
		$name = webpath($request->action);
		$res = $this->resources(true);
		
		if (isset($res[$name])) {
			/* output file */
			
			/* check time */
			$lastmodified = $res[$name]->whenModified();
			if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE']) && $lastmodified && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $lastmodified) {
				/* file didn't update */
				header("HTTP/1.1 304 Not Modified", true, 304);
				exit;
			}
			
			/* headers */
			header("Last-Modified: ".date(DATE_RFC1123, $lastmodified));
			header('Content-Type: '.$res[$name]->mime);
			
			/* content */
			echo $res[$name]->getContent();
			exit;
		}
	}
}
?>