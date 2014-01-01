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
use \LonelyGallery\Lonely as Lonely;
class Module extends \LonelyGallery\Module {
	
	/* returns html to display at the bottom of album index pages */
	public function albumBottomHtmlEvent(\LonelyGallery\Album $album) {
		return count($album->getFiles()) ? "<p><a href=\"".Lonely::escape(Lonely::model()->rootScript.$album->getPath())."large\">Large album view</a></p>\n\n" : "";
	}
	
	/* show all images of an album */
	public function lonelyLargeAction(\LonelyGallery\Request $request) {
		$lonely = Lonely::model();
		
		$album = \LonelyGallery\Factory::createAlbum($request->album);
		$file = \LonelyGallery\Factory::createFile($request->file, $album);
		
		/* file requested */
		if ($file && $file->isAvailable()) {
			$lonely->error();
		}
		
		/* album requested */
		if ($album->isAvailable()) {
			$html = '';
			
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
				$html .= "<nav class=\"breadcrumbs\">\n".
					"\t<ul>\n";
				foreach (array_reverse($parents) as $element) {
					$path = $element->getPath();
					$html .= "\t\t<li><a href=\"".Lonely::escape($path == '' ? $lonely->rootScriptClean : $lonely->rootScript.$path)."\">".Lonely::escape($element->getName())."</a></li>\n";
				}
				$path = $album->getPath();
				$html .= "\t\t<li><a href=\"".Lonely::escape($path == '' ? $lonely->rootScriptClean : $lonely->rootScript.$path)."\">".Lonely::escape($album->getName())."</a></li>\n".
					"\t</ul>\n".
					"</nav>\n\n";
			}
			
			/* album text */
			$albumText = $album->getText();
			$html .= $albumText ? '<div id="album-text">'.$albumText."</div>\n" : '';
			
			/* files */
			$action = $lonely->defaultFileAction;
			if (count($files = $album->getFiles())) {
				foreach ($files as $file) {
					
					/* image */
					$name = Lonely::escape($file->getName());
					$html .= "<div class=\"image\">\n";
					
					$html .= "\t<div id=\"".$file->getId()."\" class=\"image-box\">\n".
						"\t\t".$file->getPreviewHTML()."\n".
						"\t</div>\n\n";
					
					/* info */
					if ($file instanceof \LonelyGallery\ContentFile) {
						$html .= "\t<div class=\"image-info\">\n".
							"\t\t<p class=\"title\"><a href=\"".Lonely::escape($lonely->rootScript.$file->getPath().'/'.$action)."\">".$name."</a></p>\n".
							"\t</div>\n";
					}
					
					$html .= "</div>\n";
				}
			}
			
			/* empty album */
			if (!count($albums) && !count($files)) {
				if (empty($request->album)) {
					$html .= "<p>This gallery is empty. Try adding some image files to the directory you placed this script in. You can also have albums by creating directories.</p>";
				} else {
					$html .= "<p>This album is empty.</p>";
				}
			}
			
			$lonely->HTMLContent = $html;
			$lonely->display();
			exit;
			
		}
		
		/* nothing requested */
		$lonely->error();
	}
}
?>