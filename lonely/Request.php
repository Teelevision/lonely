<?php
/*
##########################
### Lonely PHP Gallery ###
##########################
###      Request       ###
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

This class provides the matching of the url.

Request url: /<scope>/<album>/<file>/<action>
Each element is optional.
While album and file refers to a real existing file or directory in the
gallery, the scope can be a virtual directory. Additional scopes could
be useful to implement new pages that refer to files or albums like
slideshows or a shop system, or even file unrelated, static pages.
The action is to provide several actions to one set of scope, album and
file. You could use actions to provide e.g. a comment section or.
Everything you could provide by scope you can also provide by action.
The main difference is that scopes are matched befor album/file and
actions after. Since you should take care that scope and action names
don't collide with albums and files, it is mainly a design question
whether to use scopes or actions.
If the album/file is not recognized as a part of the gallery, it is
matched as action.
Examples:
	/thumb/300px/Holiday/2013/01.jpg
		scope: thumb/300px
		album: Holiday/2013
		file: 01.jpg
		action: index
	/ABC.png/comments/new
		scope: lonely
		album (empty)
		file: ABC.png
		action: /comments/new
*/

namespace LonelyGallery;

class Request extends Component {
	
	/* scope, defaults to 'lonely' */
	public $scope = array('lonely');
	/* album, defaults to none */
	public $album = array();
	/* file, defaults to none */
	public $file = '';
	/* action, defaults to '' */
	public $action = array('');
	
	
	function __construct($rootDir, $scopePatterns) {
		
		/* get request string */
		if (isset($_SERVER['QUERY_STRING'])) {
			$request = rawurldecode($_SERVER['QUERY_STRING']);
		} else {
			$request = rawurldecode($_SERVER['REQUEST_URI']);
			if (strpos($request, $_SERVER['SCRIPT_NAME']) === 0) {
				/* cut off path and filename of this script */
				$request = substr($request, 1 + strlen($_SERVER['SCRIPT_NAME']));
			} else {
				/* cut off path of this script */
				$request = substr($request, 1 + strlen(dirname($_SERVER['SCRIPT_NAME'])));
			}
		}
		
		/* convert to array and remove empty entries, then rebuild keys */
		$requestArray = array_values(array_diff(explode('/', $request), array('')));
		
		/* match scope */
		if (preg_match_any((array)$scopePatterns, implode('/', $requestArray), $match)) {
			$this->scope = explode('/', $match[1]);
			$requestArray = array_slice($requestArray, count($this->scope));
		}
		
		/* match album, file and action */
		/* search for the longest path that is a file or dir */
		$num = count($requestArray);
		for ($i = 0; $i <= $num; ++$i) {
			
			$path = $rootDir.DIRECTORY_SEPARATOR.implode(DIRECTORY_SEPARATOR, array_slice($requestArray, 0, $num - $i));
			
			/* check if file */
			if (@is_file($path)) {
				$pos = max(0, $num - $i - 1);
				$this->album = array_slice($requestArray, 0, $pos);
				$this->file = $requestArray[$pos];
				if ($num > ($pos + 1)) {
					$this->action = array_slice($requestArray, $pos + 1);
				}
				break;
			}
			if (@is_dir($path)) {
				$pos = $num - $i;
				$this->album = array_slice($requestArray, 0, $pos);
				if ($num > $pos) {
					$this->action = array_slice($requestArray, $pos);
				}
				break;
			}
			
		}
	}
}
?>