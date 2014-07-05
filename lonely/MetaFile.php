<?php
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