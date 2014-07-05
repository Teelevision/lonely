<?php
/*
##########################
### Lonely PHP Gallery ###
##########################
### helpful functions  ###
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

Some generic helpful functions.
*/

namespace LonelyGallery;

/* help functions */

/* returns whether any of the patterns is matched */
function preg_match_any(Array $patterns, $value, &$match = null) {
	foreach ($patterns as $pattern) {
		if (preg_match($pattern, $value, $match)) {
			return true;
		}
	}
	return false;
}

/* builds a path */
function path(Array $dirs) {
	return implode(DIRECTORY_SEPARATOR, $dirs);
}
?>