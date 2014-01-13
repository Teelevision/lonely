<?php
/*
###############################
### Large Album View Module ###
###           for           ###
###    Lonely PHP Gallery   ###
###############################

### Version ###

1.1.0 beta 1
date: 2013-12-09

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

The Large Album View Module adds a page containing all images of an album in
full size. It also adds a link to this page at the bottom of each album.

### Installation ###

Place the PHP file into the 'config' directory.

*/

namespace LonelyGallery\LargeAlbumViewModule;
use \LonelyGallery\Lonely,
	\LonelyGallery\Request,
	\LonelyGallery\Factory,
	\LonelyGallery\Album,
	\LonelyGallery\ContentFile;
class Module extends \LonelyGallery\Module {
	private $_styleInitialized;
	
	/* includes css and js to the page */
	public function initStyle() {
		if (!$this->_styleInitialized) {
			Lonely::model()->cssfiles[] = Lonely::model()->configScript.'largealbumview/main.css';
			$this->_styleInitialized = true;
		}
	}
	
	/* config files */
	public function configAction(Request $request) {
		if (count($request->action) > 1 && $request->action[0] == 'largealbumview') {
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
#content > .album.largealbumview {
	margin: 0;
}
#content > .album.largealbumview > .file {
	margin-left: 0;
	margin-right: 0;
}
#content > .album.largealbumview > *:not(.file) {
	margin-left: 8px;
	margin-right: 8px;
}
#content > .album.largealbumview > .file {
	margin-bottom: 20px;
	margin-top: 20px;
}
#content > .album.largealbumview > .file > .preview-box {
	line-height: 100%;
	min-height: 0;
}
<?php
		exit;
	}
	
	/* returns links displayed at top of albums */
	public function albumLinksEvent(Album $album) {
		return count($album->getFiles()) ? array(array('url' => Lonely::model()->rootScript.$album->getPath().'large', 'label' => 'large')) : array();
	}
	
	/* show all images of an album */
	public function lonelyLargeAction(Request $request) {
		$lonely = Lonely::model();
		
		/* file requested */
		if ($request->file) {
			$lonely->error();
		}
		
		$album = Factory::createAlbum($request->album);
		
		/* album requested */
		if ($album->isAvailable()) {
			Lonely::model()->getModule('LargeAlbumViewModule')->initStyle();
			
			$html = "<section class=\"album largealbumview\">\n\n";
			
			/* parent albums */
			$parents = $album->getParents();
			
			/* title */
			$title = $album->getName();
			foreach ($parents as $element) {
				$title .= " - ".$element->getName();
			}
			$lonely->HTMLTitle = $title;
			
			/* breadcrumbs */
			if (count($parents)) {
				$html .= "\t<header>\n".
					"\t\t<ul class=\"breadcrumbs\">\n";
				foreach (array_reverse($parents) as $element) {
					$path = $element->getPath();
					$html .= "\t\t\t<li><a href=\"".Lonely::escape($path == '' ? $lonely->rootScriptClean : $lonely->rootScript.$path)."\">".Lonely::escape($element->getName())."</a></li>\n";
				}
				$html .= "\t\t\t<li>".Lonely::escape($album->getName())."</li>\n".
					"\t\t</ul>\n".
					"\t</header>\n\n";
			}
			
			/* album text */
			$albumText = $album->getText();
			$html .= $albumText ? "\t<div class=\"album-text\">".$albumText."</div>\n\n" : "";
			
			/* links */
			$html2 = "";
			foreach ($lonely->callEvent('albumLinks', $album) as $name => $datas) {
				foreach ($datas as $data) {
					if ($name == 'LargeAlbumViewModule') {
						$html2 .= "\t\t<li class=\"active\"><span>".Lonely::escape($data['label'])."</span></li>\n";
					} else {
						$html2 .= "\t\t<li><a href=\"".Lonely::escape($data['url'])."\">".Lonely::escape($data['label'])."</a></li>\n";
					}
				}
			}
			if ($html2 != "") {
				$html .= "\t<ul class=\"links\">\n".
					"\t\t<li><a href=\"".Lonely::escape(Lonely::model()->rootScript.$album->getPath())."\">index</a></li>\n".
					$html2.
					"\t</ul>\n\n";
			}
			
			/* files */
			$action = $lonely->defaultFileAction;
			if (count($files = $album->getFiles())) {
				foreach ($files as $file) {
					
					/* image */
					$name = Lonely::escape($file->getName());
					$html .= "\t<section class=\"file\">\n";
					
					/* preview */
					$html .= "\t\t<div id=\"".$file->getId()."\" class=\"preview-box\">\n".
						"\t\t\t".$file->getPreviewHTML()."\n".
						"\t\t</div>\n\n";
					
					/* info */
					if ($file instanceof ContentFile || $file->showTitle) {
						$html .= "\t\t<div class=\"info\">\n".
							"\t\t\t<p class=\"title\">\n".
							"\t\t\t\t<a href=\"".Lonely::escape($lonely->rootScript.$file->getPath().'/'.$action)."\">".$name."</a>\n".
							"\t\t\t</p>\n".
							"\t\t</div>\n";
					}
					
					$html .= "\t</section>\n";
				}
			}
			
			/* empty album */
			else if (!count($albums)) {
				if (empty($request->album)) {
					$html .= "\t<p>This gallery is empty. Try adding some image files to the directory you placed this script in. You can also have albums by creating directories.</p>\n\n";
				} else {
					$html .= "\t<p>This album is empty.</p>\n\n";
				}
			}
			
			$html .= "</section>\n";
			
			$lonely->HTMLContent = $html;
			$lonely->display();
			exit;
			
		}
		
		/* nothing requested */
		$lonely->error();
	}
}
?>