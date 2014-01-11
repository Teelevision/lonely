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
	\LonelyGallery\MetaFile;
class Module extends \LonelyGallery\Module {
	
	/* returns settings for default design */
	public function afterConstruct() {
		Lonely::model()->cssfiles[] = Lonely::model()->configScript.'youtube/main.css';
	}
	
	/* returns array of file classes to priority */
	public function fileClasses() {
		return array(
			'YouTubeTextFile' => 9,
		);
	}
	
	/* config files */
	public function configAction(Request $request) {
		if (count($request->action) > 1 && $request->action[0] == 'youtube') {
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
.youtubemodule-prev ~ a.prev {
	width: 250px;
	margin-left: -250px;
}
.youtubemodule-prev ~ a.next {
	width: 250px;
	margin-right: -250px;
}
.youtubemodule-prev ~ a.prev:before {
	text-align: right;
}
.youtubemodule-prev ~ a.next:after {
	text-align: left;
}
.youtubemodule-thumb > *:first-child {
	margin-top: 0;
}
.youtubemodule-thumb > *:last-child {
	margin-bottom: 0;
}
#images li .youtubemodule-thumb + a {
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
#images li .youtubemodule-thumb + a span {
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
class YouTubeTextFile extends MetaFile {
	private $_vData;
	
	/* whether to show the title */
	public $showTitle = true;
	
	
	/* file pattern */
	public static function pattern() {
		return '#\.[-_[:alnum:]]{11}(\.\d+s?(-\d+s?)?)?\.youtube\.txt$#i';
	}
	
	/* returns the data about this video */
	private function getVData() {
		if ($this->_vData === null) {
			preg_match('#^(?P<name>.+)\.(?P<vid>[-_[:alnum:]]{11})(\.(?P<start>\d+)s?(-(?P<end>\d+)s?)?)?\.youtube\.txt$#i', $this->getFilename(), $match);
			$this->_vData = array(
				'name' => $match['name'],
				'vid' => $match['vid'],
				'start' => (int)$match['start'],
				'end' => (int)$match['end'],
			);
		}
		return $this->_vData;
	}
	
	/* loads the source location for the thumbnail */
	public function loadThumbSourceLocation() {
		$v = $this->getVData();
		$tmpfile = tempnam(sys_get_temp_dir(), 'lonely_youtube');
		$url = 'http://img.youtube.com/vi/'.$v['vid'].'/default.jpg';
		if (($h = @fopen($url, 'r')) && file_put_contents($tmpfile, $h)) {
			return $tmpfile;
		}
		return '';
	}
	
	/* returns the data about this video */
	private function getVideoCode($width, $height, $urlData = '') {
		$v = $this->getVData();
		$url = $v['vid'] == '' ? '' : '//www.youtube-nocookie.com/v/'.$v['vid'].'?version=3&amp;rel=0'.($v['start'] ? '&amp;start='.$v['start'] : '').($v['end'] ? '&amp;end='.$v['end'] : '').$urlData;
		return "<div class=\"youtubemodule-prev\"><object width=\"".$width."\" height=\"".$height."\"><param name=\"movie\" value=\"".$url."\"><param name=\"allowFullScreen\" value=\"true\"><param name=\"allowscriptaccess\" value=\"always\"><embed src=\"".$url."\" type=\"application/x-shockwave-flash\" width=\"".$width."\" height=\"".$height."\" allowscriptaccess=\"always\" allowfullscreen=\"true\"></object></div>";
	}
	
	/* loads the name of this element */
	protected function loadName() {
		$v = $this->getVData();
		$name = strtr($v['name'], '_', ' ');
		return $name;
	}
	
	/* returns the HTML code for the preview */
	public function getPreviewHTML() {
		return "<div class=\"youtubemodule-prev\">".$this->getVideoCode(700, 394)."</div>";
	}
	
	/* returns the HTML code for the thumbnail */
	public function getThumbHTML($mode) {
		return "<div class=\"youtubemodule-thumb\">".$this->getVideoCode(300, 260, '&amp;showinfo=0&amp;controls=1')."</div>";
	}
}
?>