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
function path(Array $p, $trailingSeparator = false) {
	return implode(DIRECTORY_SEPARATOR, $p).($trailingSeparator ? DIRECTORY_SEPARATOR : '');
}
function unpath($path) {
	return explode(DIRECTORY_SEPARATOR, $path);
}

/* builds a web path */
function webpath(Array $p, $trailingSeparator = false) {
	return implode('/', $p).($trailingSeparator ? '/' : '');
}
function unwebpath($path) {
	return explode('/', $path);
}

/* makes a string UTF-8 */
function utf8ify($string) {
	$encoding = mb_detect_encoding($string, array('UTF-8', 'ISO-8859-1', 'WINDOWS-1252'));
	if ($encoding != 'UTF-8') {
		$string = iconv($encoding, 'UTF-8//TRANSLIT', $string);
	}
	return $string;
}

/* reduces a string so it only contains alphanumeric chars, dashes and underscores */
function simplifyString($string) {
	return preg_replace('#[^-_[:alnum:]]#', '_', $string);
}

/* HTML escapes a string */
function escape($string) {
	$text = htmlentities(@iconv('UTF-8', 'UTF-8//IGNORE', $string), ENT_QUOTES, 'UTF-8');
	/* umlauts workaround, https://bugs.php.net/bug.php?id=61484 */
	if ($text == '' && $string != '') {
		return preg_replace('#[^-_[:alnum:] ]#', '_', $string);
	}
	return $text;
}
?>