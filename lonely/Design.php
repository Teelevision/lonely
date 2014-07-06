<?php
/*
##########################
### Lonely PHP Gallery ###
##########################
###       Design       ###
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

A Design is a special Module that sets CSS files and render profiles.
*/

namespace LonelyGallery;

/* class to extend when developing a design */
abstract class Design extends Module {
	
	/* returns an array of thumbnail profiles */
	public function renderProfiles() {
		return array(
			'default/146px' => array(
				'width' => 146,
				'square' => true,
			),
			'default/300px' => array(
				'max-width' => 300,
				'square' => true,
			),
			'default/700px' => array(
				'max-width' =>700,
			),
		);
	}
	
	/* returns which profile to use for thumbnail of the given file/album */
	public function thumbProfile(Element $element) {
		return $element instanceof Album ? 'default/146px' : 'default/300px';
	}
	
	/* returns which profile to use for a preview of the given file */
	public function previewProfile(Element $element) {
		return 'default/700px';
	}
}
?>