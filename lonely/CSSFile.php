<?php
/*
##########################
### Lonely PHP Gallery ###
##########################
###      CSS File      ###
##########################
This file is part of the the Lonely Gallery.

### Version ###

1.1.0 dev
date: 2014-07-06

### License & Requirements & More ###

Copyright (c) 2014 Marius 'Teelevision' Neugebauer.
See LICENSE.txt, README.txt
and https://github.com/Teelevision/lonely

### Description ###

This class represents a css file.
*/

namespace LonelyGallery;

abstract class CSSFile extends ResourceFile {
	
	/* content type */
	public $mime = 'text/css';
	
	/* media attribute of <link> html tag */
	public $media = '';
	
}
?>