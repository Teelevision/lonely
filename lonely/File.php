<?php
namespace LonelyGallery;

abstract class File extends Element {
	
	/* filename on the file system */
	private $_filename;
	
	
	function __construct($gPath, $filename, Album $parent) {
		$this->_filename = $filename;
		parent::__construct($gPath, $parent);
		
		$this->initId('file_'.$this->_filename);
		if ($this->_filename !== "") {
			$parent = $this->getParent();
			$this->location = $parent->getLocation().$this->_filename;
			$this->thumbLocationPattern = $parent->getThumbPathPattern().$this->_filename;
			$this->path = $parent->getPath().rawurlencode($this->_filename);
		}
	}
	
	/* file pattern */
	public static function pattern() {
		return '/^$/'; // empty pattern to match nothing
	}
	
	/* loads the name of this element */
	protected function loadName() {
		if (($altname = $this->getAlternativeName()) !== null) {
			return $altname;
		}
		return $this->getFilename();
	}
	
	/* returns the filename */
	public function getFilename() {
		return $this->_filename;
	}
	
	/* returns the HTML code for the preview */
	public function getPreviewHTML() {
		return "";
	}
}
?>