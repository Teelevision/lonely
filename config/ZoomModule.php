<?php
/*
##########################
###    Zoom Module     ###
###        for         ###
### Lonely PHP Gallery ###
##########################

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



### Installation ###

Place the PHP file into the 'config' directory.

*/

namespace LonelyGallery\ZoomModule;
use \LonelyGallery\Lonely as Lonely;
class Module extends \LonelyGallery\Module {
	
	/* returns settings for default design */
	public function afterConstruct() {
		Lonely::model()->jsfiles[] = Lonely::model()->configScript.'zoom/main.js';
	}
	
	/* config files */
	public function configAction(\LonelyGallery\Request $request) {
		if ($request->action == array('zoom', 'main.js')) {
			$this->displayJS();
		}
	}
	
	/* lonely.js */
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
	var image = document.getElementById('image');
	var img = image.getElementsByTagName('img');
	if (img.length > 0 && (window.innerHeight <= img[0].height || window.innerWidth <= img[0].width)) {
		var aZoom = document.createElement('a');
		aZoom.href = '#';
		aZoom.innerHTML = '^';
		aZoom.style.fontSize = '80px';
		aZoom.onclick = function(){
			zoom_div = document.createElement('div');
			zoom_div.id = 'zoombox';
			zoom_div.style.position = 'fixed';
			zoom_div.style.top = 0;
			zoom_div.style.left = 0;
			zoom_div.style.width = '100%';
			zoom_div.style.height = '100%';
			zoom_div.style.overflow = 'hidden';
			zoom_div.style.margin = 0;
			zoom_div.style.textAlign = 'center';
			zoom_div.style.backgroundColor = '#111';
			zoom_div.style.cursor = 'none';
			zoom_div.onclick = function(){
				window.removeEventListener('mousemove', zoomPos);
				document.body.removeChild(zoom_div);
			};
			zoom_img = img[0].cloneNode();
			zoom_img.style.position = 'relative';
			zoom_img.style.width = '';
			zoom_img.style.height = '';
			zoom_img.style.maxHeight = '';
			zoom_div.appendChild(zoom_img);
			zoomPos({clientX: window.innerWidth/2, clientY: window.innerHeight/2});
			document.body.appendChild(zoom_div);
			window.addEventListener('mousemove', zoomPos);
			return false;
		};
		aZoom.style.width = '20%';
		aZoom.style.left = '40%';
		image.appendChild(aZoom);
	}
}
function zoomPos(event) {
	if (window.innerWidth <= zoom_img.width) {
		var x = Math.min(1, (event.clientX - 100) / (window.innerWidth - 200)) * (zoom_img.width - window.innerWidth);
		zoom_img.style.marginLeft = '-'+x+'px';
	}
	if (window.innerHeight <= zoom_img.height) {
		var y = Math.min(1, (event.clientY - 100) / (window.innerHeight - 200)) * (zoom_img.height - window.innerHeight);
		zoom_img.style.marginTop = '-'+y+'px';
	} else {
		zoom_img.style.marginTop = '-'+zoom_img.height/2+'px';
		zoom_img.style.top = '50%';
	}
}
window.addEventListener('load', initZoom);
// window.addEventListener('resize', adjustMaxImageHeight);
<?php
		exit;
	}
}
?>