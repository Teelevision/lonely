<?php
/*
##########################
###    Zoom Module     ###
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

Provides a JavaScript Zoom for images that don't fit the screen.

### Installation ###

Place the PHP file into the 'config' directory.

*/

namespace LonelyGallery\ZoomModule;
use \LonelyGallery\Lonely as Lonely;
class Module extends \LonelyGallery\Module {
	
	/* returns settings for default design */
	public function afterConstruct() {
		Lonely::model()->jsfiles[] = Lonely::model()->configScript.'zoom/main.js';
		Lonely::model()->cssfiles[] = Lonely::model()->configScript.'zoom/main.css';
	}
	
	/* config files */
	public function configAction(\LonelyGallery\Request $request) {
		if (count($request->action) > 1 && $request->action[0] == 'zoom') {
			switch ($request->action[1]) {
				case 'main.js': $this->displayJS();
				case 'main.css': $this->displayCSS();
			}
		}
	}
	
	/* main.js */
	public function displayJS() {
		$lastmodified = filemtime(__FILE__);
		if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE']) && $lastmodified && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $lastmodified) {
			header("HTTP/1.1 304 Not Modified", true, 304);
			exit;
		}
		
		header("Last-Modified: ".date(DATE_RFC1123, $lastmodified));
		header('Content-Type: text/javascript');
		?>
var zoom_img, zoom_div;
function initZoom() {
	var divs = document.getElementById('content').getElementsByTagName('div');
	for (var i = 0; i < divs.length; ++i) {
		var div = divs[i];
		if (div.className && div.className == 'image') {
			var image = div.getElementsByTagName('div')[0];
			var img = image.getElementsByTagName('img');
			if (img.length > 0 && (window.innerHeight <= img[0].naturalHeight || window.innerWidth <= img[0].naturalWidth)) {
				var aZoom = document.createElement('a');
				aZoom.href = '#';
				aZoom.className = 'zoombox-tr';
				aZoom.onclick = function(img){
					return function(){
						zoom_div = document.createElement('div');
						zoom_div.id = 'zoombox';
						zoom_div.onclick = function(){
							window.removeEventListener('mousemove', zoomPos);
							document.body.removeChild(zoom_div);
						};
						zoom_img = document.createElement('img');
						zoom_img.src = img.src;
						zoom_div.appendChild(zoom_img);
						zoomPos({clientX: window.innerWidth/2, clientY: window.innerHeight/2});
						document.body.appendChild(zoom_div);
						window.addEventListener('mousemove', zoomPos);
						return false;
					};
				}(img[0]);
				image.appendChild(aZoom);
			}
		}
	}
}
function zoomPos(event) {
	if (window.innerWidth <= zoom_img.naturalWidth) {
		var x = Math.min(1, (event.clientX - 100) / (window.innerWidth - 200)) * (zoom_img.naturalWidth - window.innerWidth);
		zoom_img.style.marginLeft = '-'+x+'px';
	}
	if (window.innerHeight <= zoom_img.naturalHeight) {
		var y = Math.min(1, (event.clientY - 100) / (window.innerHeight - 200)) * (zoom_img.naturalHeight - window.innerHeight);
		zoom_img.style.marginTop = '-'+y+'px';
	} else {
		zoom_img.style.marginTop = '-'+zoom_img.naturalHeight/2+'px';
		zoom_img.style.top = '50%';
	}
}
window.addEventListener('load', initZoom);
<?php
		exit;
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
div#zoombox {
	position: fixed;
	top: 0;
	left: 0;
	width: 100%;
	height: 100%;
	overflow: hidden;
	margin: 0;
	text-align: center;
	background-color: #111;
	cursor: none;
}
div#zoombox img {
	position: relative;
	width: auto;
	height: auto;
}
a.zoombox-tr {
	width: 20%;
	left: 40%;
}
a.zoombox-tr:after {
    content: "+";
    display: block;
    font-size: 80px;
    margin-top: -40px;
    position: absolute;
    top: 50%;
    width: 100%;
}
<?php
		exit;
	}
}
?>