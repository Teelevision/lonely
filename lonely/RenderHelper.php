<?php
/*
##########################
### Lonely PHP Gallery ###
##########################
###   Render Helper    ###
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

The Render Helper provides easy access to specific render scenarios.
It is initialized with a profile that defines the output formats. It
then handles rendering of images and albums.
*/

namespace LonelyGallery;

class RenderHelper extends Renderer {
	
	/* available profiles */
	private static $_profiles = array();
	
	/* current profile */
	protected $profile;
	
	/* instances */
	private static $_instances = array();
	
	
	/* init with profile name */
	public function __construct($profile) {
		$this->profile = $profile;
		parent::__construct(self::$_profiles[$profile]);
	}
	
	/* return instance of this class with the given profile */
	public static function profile($profile) {
		if (!isset(self::$_instances[$profile])) {
			self::$_instances[$profile] = new static($profile);
		}
		return self::$_instances[$profile];
	}
	
	/* add profiles in the form array(name => settings, ...) */
	public static function addProfiles($profiles) {
		self::$_profiles += $profiles;
	}
	
	/* render thumbnail from image */
	public function renderThumbnailOfElement(Element $file, $sourcePath = null) {
		return parent::renderThumbnail($sourcePath ?: $file->location, $file->getThumbLocation($this->profile));
	}
	
	/* returns whether the given file matches the requirements */
	public function renderChessboardOfAlbum(Album $album, Array $files) {
		return parent::renderChessboard($files, $album->getThumbLocation($this->profile));
	}
}
?>