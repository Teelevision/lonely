<?php
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