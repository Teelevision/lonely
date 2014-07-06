<?php
/*
##########################
### Lonely PHP Gallery ###
##########################
###     Asset File     ###
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

This is the abstract base class resource files that are loaded on the
website.
*/

namespace LonelyGallery;

abstract class AssetFile {
	
	/* returns when this file was updated last */
	abstract public function whenModified();
	
	/* returns the content of the file */
	abstract public function getContent();
}
?>