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
	
	/* returns settings for default design */
	public function afterConstruct() {
		Lonely::model()->cssfiles[] = Lonely::model()->configScript.'text/main.css';
	}
	
	/* returns array of file classes to priority */
	public function fileClasses() {
		return array(
			'SnippetTextFile' => 9,
		);
	}
	
	/* config files */
	public function configAction(Request $request) {
		if (count($request->action) > 1 && $request->action[0] == 'text') {
			switch ($request->action[1]) {
				case 'main.css': $this->displayCSS();
			}
		}
	}
	
	/* main.css */
	public function displayCSS() {
		$lastmodified = filemtime(__FILE__);
		if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE']) && $lastmodified && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $lastmodified) {
			header("HTTP/1.1 304 Not Modified", true, 304);
			exit;
		}
		
		header("Last-Modified: ".date(DATE_RFC1123, $lastmodified));
		header('Content-Type: text/css');
		?>
.textmodule-thumb, .textmodule-prev {
	text-align: justify;
	padding: 9px;
	border: 1px solid #333;
}
.textmodule-prev {
	max-width: 700px;
}
.textmodule-prev ~ a.prev {
	width: 250px;
	margin-left: -250px;
}
.textmodule-prev ~ a.next {
	width: 250px;
	margin-right: -250px;
}
.textmodule-prev ~ a.prev:before {
	text-align: right;
}
.textmodule-prev ~ a.next:after {
	text-align: left;
}
.textmodule-thumb {
	line-height: 20px;
	overflow: hidden;
	height: 280px;
	width: 280px;
}
.textmodule-thumb > *:first-child {
	margin-top: 0;
}
.textmodule-thumb > *:last-child {
	margin-bottom: 0;
}
#images li .textmodule-thumb + a {
	opacity: 1;
	width: 298px;
	left: 1px;
	bottom: 1px;
	height: 10px;
	line-height: 40px;
	top: auto;
	padding: 20px 0 0;
	background-color: #000;
	background: linear-gradient(rgba(0,0,0,0), rgba(0,0,0,1) 50%);
	transition: height .15s ease .15s;
}
#images li:hover .textmodule-thumb + a {
	height: 40px;
	transition: height .15s ease;
}
#images li .textmodule-thumb + a span {
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
#images li:hover .textmodule-thumb + a span {
	opacity: 1;
	transition: opacity .15s ease .15s;
}
<?php
		exit;
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
		$text = file_get_contents($this->location);
		if (substr($this->getFilename(), -3) == 'txt') {
			$text = nl2br(Lonely::model()->escape($text), false);
		}
		return "<div class=\"textmodule-prev\">".$text."</div>";
	}
	
	/* returns the HTML code for the thumbnail */
	public function getThumbHTML($mode) {
		$text = file($this->location, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
		$text = substr($this->getFilename(), -3) == 'txt' ? Lonely::model()->escape($text[0]) : $text[0];
		return "<div class=\"textmodule-thumb\">".$text."</div>";
	}
}
?>