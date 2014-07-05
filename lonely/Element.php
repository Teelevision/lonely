<?php
/*
##########################
### Lonely PHP Gallery ###
##########################
###      Element       ###
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

The Element represents an file system entity like a file or an album.
*/

namespace LonelyGallery;

abstract class Element extends Component {
	
	/* unique path within the gallery */
	private $_galleryPath;
	
	/* reference to the parent album */
	private $_parent;
	
	/* absolute location on the filesystem */
	protected $location;
	
	/* absolute thumb location on the filesystem, containing "<profile>" which is replaced by the actual mode */
	protected $thumbLocationPattern;
	
	/* relative web path */
	protected $path;
	
	/* cached name of this element */
	private $_name;
	
	/* cached alternative names */
	private $_altNames;
	
	/* unique string that can identify this element */
	private $_id;
	private static $_usedIds = array();
	
	
	function __construct(Array $galleryPath, Album $parent = null) {
		$this->_galleryPath = $galleryPath;
		$this->_parent = $parent;
	}
	
	/* create id */
	protected static function createId($name) {
		$i = 1;
		do {
			$id = Lonely::simplifyString($name).($i++ > 1 ? '_'.$i : '');
		} while (in_array($id, self::$_usedIds));
		return $id;
	}
	
	/* inits the id */
	protected function initId($name) {
		self::$_usedIds[] = $this->_id = self::createId($name);
	}
	
	/* returns the gallery path */
	public function getGalleryPath() {
		return $this->_galleryPath;
	}
	
	/* returns the id */
	public function getId() {
		return $this->_id;
	}
	
	/* check if the file or directory is available */
	function isAvailable() {
		return @is_readable($this->location);
	}
	
	/* sets the parent album */
	protected function setParent(Album $parent) {
		$this->_parent = $parent;
	}
	
	/* returns the object of the parent album */
	public function getParent() {
		return $this->_parent;
	}
	
	/* returns an array of the parent albums */
	public function getParents() {
		$albums = array();
		for ($album = $this; $album = $album->getParent();) {
			$albums[] = $album;
		}
		return $albums;
	}
	
	/* returns the absolute file location */
	public function getLocation() {
		return $this->location;
	}
	
	/* returns the absolute thumb file location pattern containing "<profile>" */
	public function getThumbPathPattern() {
		return $this->thumbLocationPattern;
	}
	
	/* returns the name of this element */
	public function getName() {
		if ($this->_name === null) {
			$this->_name = $this->loadName();
		}
		return $this->_name;
	}
	
	/* loads the name of this element */
	protected function loadName() {
		return '';
	}
	
	/* returns the alternative names for this file */
	protected function getAlternativeNames() {
		if ($this->_altNames === null) {
			$this->_altNames = array();
			foreach (Lonely::model()->callEvent('elementNames', $this) as $data) {
				if ($data !== null) {
					$this->_altNames[] = $data;
				}
			}
		}
		return $this->_altNames;
	}
	
	/* returns the first alternative name for this file or null */
	protected function getAlternativeName() {
		$altNames = $this->getAlternativeNames();
		return count($altNames) ? $altNames[0] : null;
	}
	
	/* returns the relative web path */
	public function getPath() {
		return $this->path;
	}
	
	/* returns the absolute path of the thumbnail */
	public function getThumbLocation($profile) {
		return strtr($this->thumbLocationPattern, array('<profile>'=>$profile));
	}
	
	/* returns the web thumb path */
	public function getThumbPath($profile) {
		return ($this->thumbAvailable($profile) ? Lonely::model()->thumbPath : Lonely::model()->thumbScript).
			$profile.'/'.$this->path;
	}
	
	/* checks if there is a up-to-date thumbnail file */
	public function thumbAvailable($profile) {
		$thumbPath = $this->getThumbLocation($profile);
		return ($thumbPath && ($tTime = @filemtime($thumbPath)) && ($oTime = @filemtime($this->location)) && $tTime >= $oTime);
	}
	
	/* initializes the thumbnail */
	public function initThumb($profile) {
		/* check if thumbnail is available and up to date */
		return ($this->thumbAvailable($profile) || $this->createThumb($profile, $this->getThumbLocation($profile)));
	}
	
	/* creates a thumbnail */
	protected function createThumb($profile, $saveTo) {
		return false;
	}
	
	/* returns the mime type */
	public function getMime() {
		return 'application/octet-stream';
	}
	
	/* returns the HTML code for the thumbnail */
	public function getThumbHTML($profile) {
		$thumbpath = Lonely::escape($this->getThumbPath($profile));
		$name = Lonely::escape($this->getName());
		return "<img class=\"thumb\" src=\"".$thumbpath."\" alt=\"".$name."\">";
	}
}
?>