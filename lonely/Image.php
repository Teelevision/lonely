<?php
/*
##########################
### Lonely PHP Gallery ###
##########################
###    Lonely Core     ###
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

The Image is the class referring to an image file.
*/

namespace LonelyGallery;

class Image extends ContentFile {
	
	private $_useOriginalAsThumb = array();
	
	
	/* file pattern */
	public static function pattern() {
		return '/\.(png|jpe?g|gif)$/i';
	}
	
	/* loads the name of this element */
	protected function loadName() {
		if (($altname = $this->getAlternativeName()) !== null) {
			return $altname;
		}
		$name = $this->getFilename();
		$name = substr($name, 0, strrpos($name, '.'));
		$name = strtr($name, '_', ' ');
		return $name;
	}
	
	/* returns the mime type */
	public function getMime() {
		$info = @getimagesize($this->location);
		return $info['mime'];
	}
	
	/* returns the HTML code for the preview */
	public function getPreviewHTML() {
		$path = empty(Lonely::model()->useOriginals) ? $this->getThumbPath(Lonely::model()->getDesign()->previewProfile($this)) : Lonely::model()->rootPath.$this->path;
		$name = Lonely::escape($this->getName());
		return "<img class=\"preview\" src=\"".Lonely::escape($path)."\" alt=\"".$name."\">\n";
	}
	
	/* returns whether this file is suitable as a thumb without resizing */
	public function canUseOriginalAsThumb($profile) {
		if (!isset($this->_useOriginalAsThumb[$profile])) {
			$this->_useOriginalAsThumb[$profile] = RenderHelper::profile($profile)->isSuitable($this->location);
		}
		return $this->_useOriginalAsThumb[$profile];
	}
	
	/* returns the absolute path of the thumbnail */
	public function getThumbLocation($profile) {
		return $this->canUseOriginalAsThumb($profile) ? $this->getLocation() : parent::getThumbLocation($profile);
	}
	
	/* returns the web thumb path */
	public function getThumbPath($profile) {
		return $this->canUseOriginalAsThumb($profile) ? Lonely::model()->rootPath.$this->path : parent::getThumbPath($profile);
	}
	
	/* checks if there is a up-to-date thumbnail file */
	public function thumbAvailable($profile) {
		return ($this->canUseOriginalAsThumb($profile) || parent::thumbAvailable($profile));
	}
	
	/* creates a thumbnail */
	protected function createThumb($profile, $saveTo) {
		return $this->canUseOriginalAsThumb($profile) || RenderHelper::profile($profile)->renderThumbnailOfElement($this);
	}
}
?>