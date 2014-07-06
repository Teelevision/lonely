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
	\LonelyGallery\Album,
	\LonelyGallery\MetaFile;
class Module extends \LonelyGallery\Module {
	
	/* whether to include the style sheet */
	public $initRes = false;
	
	/* returns array of file classes to priority */
	public function fileClasses() {
		return array(
			'VimeoTextFile' => 9,
		);
	}
	
	/* returns an array with config-relative web paths to ResourceFile instances */
	public function resources($forceAll = false) {
		return ($forceAll || $this->initRes) ? array(
			'vimeo/main.css' => new CSSFile(),
		) : array();
	}
}
class VimeoTextFile extends MetaFile {
	private $_v;
	protected $_deleteThumbOnDestruct = false;
	
	/* whether to show the title */
	public $showTitle = true;
	
	
	function __construct($gPath, $filename, Album $parent) {
		parent::__construct($gPath, $filename, $parent);
		preg_match('#^(?P<name>.+)\.(?P<vid>\d+)\.vimeo\.txt$#i', $this->getFilename(), $match);
		$this->_v = array(
			'name' => $match['name'],
			'vid' => $match['vid'],
		);
		$this->thumbLocationPattern = Lonely::model()->thumbDir.'vimeo/<profile>'.DIRECTORY_SEPARATOR.$this->_v['vid'].'.jpg';
	}
	
	/* file pattern */
	public static function pattern() {
		return '#\.\d+\.vimeo\.txt$#i';
	}
	
	/* loads the source location for the thumbnail */
	public function loadThumbSourceLocation() {
		$profile = 'small';
		if ($this->initThumb($profile)) {
			return $this->getThumbLocation($profile);
		}
		return '';
	}
	
	/* creates a thumbnail */
	protected function createThumb($profile, $saveTo) {
		$infoUrl = 'http://vimeo.com/api/v2/video/'.$this->_v['vid'].'.json';
		if (($json = @file_get_contents($infoUrl)) && ($info = json_decode($json, true)) && isset($info[0]['thumbnail_small'])) {
			$url = $info[0]['thumbnail_small'];
		} else {
			return false;
		}
		
		/* create dir */
		$dir = dirname($saveTo);
		if (!is_dir($dir)) {
			mkdir($dir, -1, true);
		}
		
		if (($h = @fopen($url, 'r')) && file_put_contents($saveTo, $h)) {
			@fclose($h);
			return true;
		}
		return false;
	}
	
	/* returns the data about this video */
	private function getVideoCode($width, $height, $urlData = '') {
		return "<iframe class=\"preview\" src=\"//player.vimeo.com/video/".$this->_v['vid']."?".$urlData."\" width=\"".$width."\" height=\"".$height."\" style=\"border: 0;\" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>";
	}
	
	/* loads the name of this element */
	protected function loadName() {
		$name = strtr($this->_v['name'], '_', ' ');
		return $name;
	}
	
	/* returns the HTML code for the preview */
	public function getPreviewHTML() {
		return "<div class=\"vimeomodule-preview preview-controls-sideways\">".$this->getVideoCode(700, 394)."</div>";
	}
	
	/* returns the HTML code for the thumbnail */
	public function getThumbHTML($mode) {
		Lonely::model()->getModule('VimeoModule')->initRes = true;
		return "<div class=\"vimeomodule-thumb\">".$this->getVideoCode(300, 260, 'badge=0&amp;byline=0&amp;portrait=0&amp;title=0')."</div>";
	}
}

class CSSFile extends \LonelyGallery\CSSFile {
	
	public function whenModified() {
		return filemtime(__FILE__);
	}
	
	public function getContent() {
		return <<<'CSS'
.album .files li .vimeomodule-thumb + a.thumb-link {
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
.album .files li .vimeomodule-thumb + a.thumb-link span {
	background-color: transparent;
	box-shadow: none;
	line-height: 38px;
	padding: 0px 5px;
	overflow: hidden;
	text-overflow: ellipsis;
	white-space: nowrap;
}
CSS;
	}
}
?>