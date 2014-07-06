<?php
/*
##########################
###   YouTube Module   ###
###        for         ###
### Lonely PHP Gallery ###
##########################

### Version ###

1.1.0 beta 1
date: 2014-01-09

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

Helps embedding YouTube videos.
Simply creates files of this pattern: name.videoID.youtube.txt

Example:
Url: https://www.youtube.com/watch?v=H7jtC8vjXw8
File: YouTube_Rewind_2013.H7jtC8vjXw8.youtube.txt

You can also add the start position by giving the offset in seconds like this:
Url: https://www.youtube.com/watch?v=G5AdrupH788#t=106
File: Guide_to_our_Galaxy.G5AdrupH788.106s.youtube.txt

Setting an end is possible, too:
Seconds 106 to 130:
Guide_to_our_Galaxy.G5AdrupH788.106-130s.youtube.txt
From beginning to 130:
Guide_to_our_Galaxy.G5AdrupH788.0-130s.youtube.txt


### Installation ###

Place the PHP file into the 'config' directory.

*/

namespace LonelyGallery\YouTubeModule;
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
			'YouTubeTextFile' => 9,
		);
	}
	
	/* returns an array with config-relative web paths to ResourceFile instances */
	public function resources($forceAll = false) {
		return ($forceAll || $this->initRes) ? array(
			'youtube/main.css' => new CSSFile(),
		) : array();
	}
}
class YouTubeTextFile extends MetaFile {
	private $_v;
	protected $_deleteThumbOnDestruct = false;
	
	/* whether to show the title */
	public $showTitle = true;
	
	
	function __construct($gPath, $filename, Album $parent) {
		parent::__construct($gPath, $filename, $parent);
		preg_match('#^(?P<name>.+)\.(?P<vid>[-_[:alnum:]]{11})(\.(?P<start>\d+)s?(-(?P<end>\d+)s?)?)?\.youtube\.txt$#i', $this->getFilename(), $match);
		$this->_v = array(
			'name' => $match['name'],
			'vid' => $match['vid'],
			'start' => (int)$match['start'],
			'end' => (int)$match['end'],
		);
		$this->thumbLocationPattern = \LonelyGallery\path(array(Lonely::model()->thumbDir.'youtube', '<profile>', $this->_v['vid'].'.jpg'));
	}
	
	/* file pattern */
	public static function pattern() {
		return '#\.[-_[:alnum:]]{11}(\.\d+s?(-\d+s?)?)?\.youtube\.txt$#i';
	}
	
	/* loads the source location for the thumbnail */
	public function loadThumbSourceLocation() {
		$profile = 'default';
		if ($this->initThumb($profile)) {
			return $this->getThumbLocation($profile);
		}
		return '';
	}
	
	/* creates a thumbnail */
	protected function createThumb($profile, $saveTo) {
		$url = 'http://img.youtube.com/vi/'.$this->_v['vid'].'/default.jpg';
		
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
		$url = $this->_v['vid'] == '' ? '' : '//www.youtube-nocookie.com/v/'.$this->_v['vid'].'?version=3&amp;rel=0'.($this->_v['start'] ? '&amp;start='.$this->_v['start'] : '').($this->_v['end'] ? '&amp;end='.$this->_v['end'] : '').$urlData;
		return "<object class=\"preview\" width=\"".$width."\" height=\"".$height."\"><param name=\"movie\" value=\"".$url."\"><param name=\"allowFullScreen\" value=\"true\"><param name=\"allowscriptaccess\" value=\"always\"><embed src=\"".$url."\" type=\"application/x-shockwave-flash\" width=\"".$width."\" height=\"".$height."\" allowscriptaccess=\"always\" allowfullscreen=\"true\"></object>";
	}
	
	/* loads the name of this element */
	protected function loadName() {
		$name = strtr($this->_v['name'], '_', ' ');
		return $name;
	}
	
	/* returns the HTML code for the preview */
	public function getPreviewHTML() {
		return "<div class=\"youtubemodule-preview preview-controls-sideways\">".$this->getVideoCode(700, 394)."</div>";
	}
	
	/* returns the HTML code for the thumbnail */
	public function getThumbHTML($mode) {
		Lonely::model()->getModule('YouTubeModule')->initRes = true;
		return "<div class=\"youtubemodule-thumb\">".$this->getVideoCode(300, 260, '&amp;showinfo=0&amp;controls=1')."</div>";
	}
}

class CSSFile extends \LonelyGallery\CSSFile {
	
	public function whenModified() {
		return filemtime(__FILE__);
	}
	
	public function getContent() {
		return <<<'CSS'
.album .files li .youtubemodule-thumb + a.thumb-link {
	opacity: 1;
	width: 300px;
	left: 0;
	bottom: 0;
	border-top: 2px solid #767676;
	top: auto;
	height: 38px;
	padding: 0;
	background-color: #1b1b1b;
	line-height: 38px;
}
.album .files li .youtubemodule-thumb + a.thumb-link span {
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