<?php
/*
##########################
###    Link Module     ###
###        for         ###
### Lonely PHP Gallery ###
##########################

### Version ###

1.1.0 beta 1
date: 2014-01-09

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

namespace LonelyGallery\LinkModule;
use \LonelyGallery\Lonely,
	\LonelyGallery\Request,
	\LonelyGallery\Factory,
	\LonelyGallery\MetaFile;
class Module extends \LonelyGallery\Module {
	
	/* returns settings for default design */
	public function afterConstruct() {
		Lonely::model()->cssfiles[] = Lonely::model()->configScript.'link/main.css';
	}
	
	/* returns array of file classes to priority */
	public function fileClasses() {
		return array(
			'LinkTextFile' => 9,
		);
	}
	
	/* config files */
	public function configAction(Request $request) {
		if (count($request->action) > 1 && $request->action[0] == 'link') {
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
a.linkmodule-prev {
	width: 100%;
}
.linkmodule-prev ~ a.prev {
	width: 250px;
	margin-left: -250px;
}
.linkmodule-prev ~ a.next {
	width: 250px;
	margin-right: -250px;
}
.linkmodule-prev ~ a.prev:before {
	text-align: right;
}
.linkmodule-prev ~ a.next:after {
	text-align: left;
}
.linkmodule-thumb + a + a {
	display: none;
}
.linkmodule-thumb + a span:before {
	content: "⇒ ";
}
<?php
		exit;
	}
}
class LinkTextFile extends MetaFile {
	private $_lData;

	/* file pattern */
	public static function pattern() {
		return '/\.link\.txt$/i';
	}
	
	/* loads the name of this element */
	protected function loadName() {
		if (($altname = $this->getAlternativeName()) !== null) {
			return $altname;
		}
		$name = $this->getFilename();
		$name = substr($name, 0, strrpos($name, '.l'));
		$name = strtr($name, '_', ' ');
		return $name;
	}
	
	/* returns the data about this link */
	private function getLData() {
		if ($this->_lData === null) {
			$lines = file($this->location, FILE_IGNORE_NEW_LINES);
			$this->_lData = array(
				'url' => isset($lines[0]) ? $lines[0] : '#',
				'image' => isset($lines[1]) ? $lines[1] : '',
				'label' => isset($lines[2]) ? $lines[2] : '',
			);
		}
		return $this->_lData;
	}
	
	/* returns the HTML code for the preview */
	public function getPreviewHTML() {
		$l = $this->getLData();
		$thumb = Factory::createFileByRelPath($l['image'], $this->getParent());
		return $thumb->getPreviewHTML()."<a class=\"linkmodule-prev\" href=\"".Lonely::escape($l['url'])."\"><span>".Lonely::escape($l['label'])."</span></a>";
	}
	
	/* returns the HTML code for the thumbnail */
	public function getThumbHTML($mode) {
		$l = $this->getLData();
		$thumb = Factory::createFileByRelPath($l['image'], $this->getParent());
		return "<div class=\"linkmodule-thumb\">".$thumb->getThumbHTML($mode)."</div><a href=\"".Lonely::escape($l['url'])."\"><span>".Lonely::escape($l['label'])."</span></a>";
	}
}
?>