<?php
/*
##########################
###    Text Module     ###
###        for         ###
### Lonely PHP Gallery ###
##########################

### Version ###

1.1.0 beta 1
date: 2013-12-24

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

Provides text snippets that work basicly like images but display text instead.

Snippets
Just create files like this: name.snip.txt
Name is irrelevant besides from the order of the files and is not displayed.
The first paragraph of the file's content will be rendered in the album view.
The full content is displayed on its own preview page.
To use HTML, name the file name.snip.html, but stick to basic HTML since it
can break the website's design.

### Installation ###

Place the PHP file into the 'config' directory.

*/

namespace LonelyGallery\TextModule;
use \LonelyGallery\Lonely,
	\LonelyGallery\Request,
	\LonelyGallery\MetaFile;
class Module extends \LonelyGallery\Module {
	
	/* whether to include the style sheet */
	public $initRes = false;
	
	/* returns array of file classes to priority */
	public function fileClasses() {
		return array(
			'SnippetTextFile' => 9,
		);
	}
	
	/* returns an array with config-relative web paths to ResourceFile instances */
	public function resources() {
		return $this->initRes ? array(
			'text/main.css' => new CSSFile(),
		) : array();
	}
}
class SnippetTextFile extends MetaFile {

	/* file pattern */
	public static function pattern() {
		return '/\.snip\.(txt|html?)$/i';
	}
	
	/* loads the name of this element */
	protected function loadName() {
		return '...';
	}
	
	/* returns the HTML code for the preview */
	public function getPreviewHTML() {
		Lonely::model()->getModule('TextModule')->initRes = true;
		$text = trim(file_get_contents($this->location));
		if (substr($this->getFilename(), -3) == 'txt') {
			$text = nl2br(\LonelyGallery\escape($text), false);
		}
		return "<div class=\"textmodule-preview preview preview-controls-sideways\">".$text."</div>";
	}
	
	/* returns the HTML code for the thumbnail */
	public function getThumbHTML($profile, &$htmlclass = '') {
		Lonely::model()->getModule('TextModule')->initRes = true;
		$text = trim(file_get_contents($this->location));
		if (substr($this->getFilename(), -3) == 'txt') {
			$text = '<p>'.nl2br(\LonelyGallery\escape($text), false).'</p>';
		}
		$classes = 'textmodule-thumb';
		if (strlen($text) > 255 || substr_count($text, '<br>') > 5) $classes .= ' textmodule-scroll';
		return "<div class=\"".$classes."\"><div>".$text."</div></div>";
	}
}

class CSSFile extends \LonelyGallery\CSSFile {
	
	public function whenModified() {
		return filemtime(__FILE__);
	}
	
	public function getContent() {
		return <<<'CSS'
.album .files li .textmodule-thumb, .file .preview-box .textmodule-preview {
	text-align: justify;
	padding: 9px;
	border: 1px solid #333;
}
.file .preview-box .textmodule-preview {
	max-width: 680px;
	line-height: 100%;
	display: inline-block;
}
.file .preview-box .textmodule-preview ~ a.prev {
	left: 50%;
	margin-left: -710px;
	width: 350px;
}
.file .preview-box .textmodule-preview ~ a.next {
	left: 50%;
	margin-left: 360px;
	width: 350px;
}
.file .preview-box .textmodule-preview ~ a.prev:before {
	text-align: right;
}
.file .preview-box .textmodule-preview ~ a.next:after {
	text-align: left;
}
.album .files li .textmodule-thumb > div {
	margin: 0;
	line-height: 20px;
	overflow: hidden;
	height: 280px;
	width: 280px;
}
.album .files li .textmodule-thumb > div > *:first-child {
	margin-top: 0;
}
.album .files li .textmodule-thumb > div > *:last-child {
	margin-bottom: 0;
}

/* caption */
.album .files li .textmodule-thumb + a {
	opacity: 1;
	width: 298px;
	left: 1px;
	bottom: 1px;
	height: 10px;
	line-height: 40px;
	top: auto;
	padding: 20px 0 0;
	background-color: #000;
	background: linear-gradient(rgba(0,0,0,0), rgba(0,0,0,1) 70%);
	transition: height .15s ease .15s;
}
.album .files li:hover .textmodule-thumb + a {
	height: 40px;
	transition: height .15s ease;
}
.album .files li .textmodule-thumb + a span {
	opacity: 0;
	background-color: transparent;
	box-shadow: none;
	padding: 16px;
	line-height: 40px;
	padding: 0px 5px;
	overflow: hidden;
	text-overflow: ellipsis;
	white-space: nowrap;
	transition: opacity .15s ease;
}
.album .files li:hover .textmodule-thumb + a span {
	opacity: 1;
	transition: opacity .15s ease .15s;
}

/* scrolling */
.album .files li .textmodule-thumb.textmodule-scroll > div {
	transition-property: height, margin-top;
}
.album .files li:hover .textmodule-thumb.textmodule-scroll > div {
	height: 2280px;
	margin-top: -2000px;
}
.album .files li .textmodule-thumb.textmodule-scroll:after {
	background-color: gray;
	content: "";
	display: block;
	height: 20px;
	position: absolute;
	right: 0;
	top: 0;
	width: 4px;
	transition-property: top;
}
.album .files li:hover .textmodule-thumb.textmodule-scroll:after {
	top: 278px;
}
.album .files li .textmodule-thumb.textmodule-scroll > div, .album .files li .textmodule-thumb.textmodule-scroll:after {
	transition-duration: 4s;
	transition-delay: 6s;
	transition-timing-function: cubic-bezier(.05,0,.6,.6);
}
.album .files li:hover .textmodule-thumb.textmodule-scroll > div, .album .files li:hover .textmodule-thumb.textmodule-scroll:after {
	transition-duration: 180s;
	transition-delay: 4s;
}
CSS;
	}
}
?>