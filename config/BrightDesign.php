<?php
/*
##########################
###   Bright Design    ###
###        for         ###
### Lonely PHP Gallery ###
##########################

### Version ###

1.1.0 beta 1
date: 2013-11-26

### Requirements ###

Lonely PHP Gallery 1.1.0 beta 1 or above

### License ###

Copyright (c) 2013 Marius 'Teelevision' Neugebauer

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

### Description ###

Adds support for many files that are not images and provides file type previews.

### Installation ###

Place the PHP file into the 'config' directory.

### Settings ###

None.

*/

namespace LonelyGallery\BrightDesign;
use \LonelyGallery\Lonely as Lonely;
class BrightDesign extends \LonelyGallery\Design {
	
	/* returns an array with css files to be loaded as design */
	public function getCSSFiles() {
		return array(
			Lonely::model()->configScript.'lonely.css',
			Lonely::model()->configScript.'design/bright.css',
		);
	}
	
	/* config files */
	public function configAction(\LonelyGallery\Request $request) {
		if ($request->action == array('design', 'bright.css')) {
			$this->displayCSS();
		}
	}
	
	/* design/bright.css */
	public function displayCSS() {
		$lastmodified = filemtime(__FILE__);
		if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE']) && $lastmodified && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $lastmodified) {
			header("HTTP/1.1 304 Not Modified", true, 304);
			exit;
		}
		
		header("Last-Modified: ".date(DATE_RFC1123, $lastmodified));
		header('Content-Type: text/css');
		?>
body {
	background-color: #eee;
	color: #111;
}
a {
	color: #b20;
}
a:hover {
	color: #000;
}
h1 a {
	color: #111;
}
.image-info p.title {
	color: #000000;
}
.image-info p.title:before, .image-info p.title:after {
	color: #aaa;
}
.image-box a {
	color: #fff;
}
<?php
		exit;
	}
}
?>