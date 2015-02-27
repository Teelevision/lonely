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
use \LonelyGallery\Lonely,
	\LonelyGallery\Request;
class Module extends \LonelyGallery\DefaultDesign\Module {
	
	/* returns an array with config-relative web paths to ResourceFile instances */
	public function resources() {
		return parent::resources() + array(
			'design/bright.css' => new CSSFile(),
		);
	}
}

class CSSFile extends \LonelyGallery\CSSFile {
	
	public function whenModified() {
		return filemtime(__FILE__);
	}
	
	public function getContent() {
		return <<<'CSS'
body {
	background-color: #eee;
	color: #111;
}
a {
	color: #02b;
}
a:hover {
	color: #000;
}
h1 a {
	color: #111;
}
.file .title {
	color: #000;
}
.file .title:before, .file .title:after {
	color: #aaa;
}
.album .files li {
	color: #fff;
}
.album > .albums > li, .album > .files > li {
	background-color: #ddd;
}
body > #zoombox {
	background-color: #eee !important;
}
.album .files li .textmodule-thumb {
	color: #111 !important;
}
.album .files li .textmodule-thumb + a {
	background-color: #ddd !important;
	background: linear-gradient(rgba(221,221,221,0), rgba(221,221,221,1) 70%) !important;
	color: #111 !important;
}
.album .files li .textmodule-thumb, .file .preview-box .textmodule-preview {
	border: 1px solid #ccc !important;
}
CSS;
	}
}
?>