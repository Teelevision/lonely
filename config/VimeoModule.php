<?php
/*
##########################
###    Vimeo Module    ###
###        for         ###
### Lonely PHP Gallery ###
##########################

### Version ###

1.1.0 beta 1
date: 2014-01-10

### Requirements ###

Lonely PHP Gallery 1.1.0 beta 1 or above

### License ###

Copyright (c) 2014 Marius 'Teelevision' Neugebauer

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

Helps embedding Vimeo videos.
Simply creates files of this pattern: name.videoID.vimeo.txt

Example:
Url: http://vimeo.com/14912890
File: Everything_is_a_Remix_Part_1.14912890.vimeo.txt

### Installation ###

Place the PHP file into the 'config' directory.

*/

namespace LonelyGallery\VimeoModule;
use \LonelyGallery\Lonely,
	\LonelyGallery\Request,
	\LonelyGallery\MetaFile;
class Module extends \LonelyGallery\Module {
	
	/* returns settings for default design */
	public function afterConstruct() {
		Lonely::model()->cssfiles[] = Lonely::model()->configScript.'vimeo/main.css';
	}
	
	/* returns array of file classes to priority */
	public function fileClasses() {
		return array(
			'VimeoTextFile' => 9,
		);
	}
	
	/* config files */
	public function configAction(Request $request) {
		if (count($request->action) > 1 && $request->action[0] == 'vimeo') {
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
.vimeomodule-thumb > iframe, .vimeomodule-prev > iframe {
	border: 0;
}
.vimeomodule-prev ~ a.prev {
	width: 250px;
	margin-left: -250px;
}
.vimeomodule-prev ~ a.next {
	width: 250px;
	margin-right: -250px;
}
.vimeomodule-prev ~ a.prev:before {
	text-align: right;
}
.vimeomodule-prev ~ a.next:after {
	text-align: left;
}
.vimeomodule-thumb > *:first-child {
	margin-top: 0;
}
.vimeomodule-thumb > *:last-child {
	margin-bottom: 0;
}
#images li .vimeomodule-thumb + a {
	opacity: 1;
	width: 300px;
	left: 0;
	bottom: 0;
	border-top: 2px solid #37B04D;
	top: auto;
	height: 38px;
	padding: 0;
	background-color: #111A19;
	line-height: 38px;
}
#images li .vimeomodule-thumb + a span {
	background-color: transparent;
	box-shadow: none;
	line-height: 38px;
	padding: 0px 5px;
	overflow: hidden;
	text-overflow: ellipsis;
	white-space: nowrap;
}
<?php
		exit;
	}
}
class VimeoTextFile extends MetaFile {
	private $_vData;
	
	/* whether to show the title */
	public $showTitle = true;
	
	
	/* file pattern */
	public static function pattern() {
		return '#\.\d+\.vimeo\.txt$#i';
	}
	
	/* returns the data about this video */
	private function getVData() {
		if ($this->_vData === null) {
			preg_match('#^(?P<name>.+)\.(?P<vid>\d+)\.vimeo\.txt$#i', $this->getFilename(), $match);
			$this->_vData = array(
				'name' => $match['name'],
				'vid' => $match['vid'],
			);
		}
		return $this->_vData;
	}
	
	/* loads the source location for the thumbnail */
	public function loadThumbSourceLocation() {
		$v = $this->getVData();
		$infoUrl = 'http://vimeo.com/api/v2/video/'.$v['vid'].'.json';
		if ($json = @file_get_contents($infoUrl)) {
			$info = json_decode($json, true);
			if (isset($info[0]['thumbnail_small'])) {
				$tmpfile = tempnam(sys_get_temp_dir(), 'lonely_vimeo');
				if (($h = @fopen($info[0]['thumbnail_small'], 'r')) && file_put_contents($tmpfile, $h)) {
					return $tmpfile;
				}
			}
		}
		return '';
	}
	
	/* returns the data about this video */
	private function getVideoCode($width, $height, $urlData = '') {
		$v = $this->getVData();
		return "<iframe src=\"//player.vimeo.com/video/".$v['vid']."?".$urlData."\" width=\"".$width."\" height=\"".$height."\" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>";
	}
	
	/* loads the name of this element */
	protected function loadName() {
		$v = $this->getVData();
		$name = strtr($v['name'], '_', ' ');
		return $name;
	}
	
	/* returns the HTML code for the preview */
	public function getPreviewHTML() {
		return "<div class=\"vimeomodule-prev\">".$this->getVideoCode(700, 394)."</div>";
	}
	
	/* returns the HTML code for the thumbnail */
	public function getThumbHTML($mode) {
		return "<div class=\"vimeomodule-thumb\">".$this->getVideoCode(300, 260, 'badge=0&amp;byline=0&amp;portrait=0&amp;title=0')."</div>";
	}
}
?>