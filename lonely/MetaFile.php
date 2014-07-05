<?php
/*
##########################
### Lonely PHP Gallery ###
##########################
###     Meta File      ###
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

The Meta File is a special File that defines what to display instead
of containing it. E.g. it could contain an url of an image to display.
*/

namespace LonelyGallery;

abstract class MetaFile extends File {
	
	/* image from which to render the thumbnail */
	private $_thumbSourceLocation;
	protected $_deleteThumbOnDestruct = true;
	
	/* whether to show the title */
	public $showTitle = false;
	
	
	function __destruct() {
		if ($this->_deleteThumbOnDestruct && $this->_thumbSourceLocation) {
			unlink($this->_thumbSourceLocation);
		}
	}
	
	/* returns the source location of the image from which the thumbnail can be rendered */
	public function getThumbSourceLocation() {
		if ($this->_thumbSourceLocation === null) {
			$this->_thumbSourceLocation = $this->loadThumbSourceLocation();
		}
		return $this->_thumbSourceLocation;
	}
	
	/* loads the source location for the thumbnail */
	public function loadThumbSourceLocation() {
		return '';
	}
}
?>