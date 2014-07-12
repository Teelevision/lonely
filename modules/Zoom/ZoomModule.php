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
use \LonelyGallery\Lonely,
	\LonelyGallery\File,
	\LonelyGallery\Image,
	\LonelyGallery\Request;
class Module extends \LonelyGallery\Module {
	
	/* whether to include the style sheet and javascript */
	public $initRes = false;
	
	/* returns settings for default design */
	public function afterConstruct() {
		Lonely::model()->useOriginals = true;
		Lonely::model()->footer .= "<script type=\"text/javascript\">
var img = document.querySelectorAll('.file img.preview');
for (var i = 0; i < img.length; ++i) {
	img[i].addEventListener('load', function(image){
		return function(){
			initImageZoom(image);
		};
	}(img[i]));
}
</script>";
	}
	
	/* returns an array with config-relative web paths to ResourceFile instances */
	public function resources() {
		return $this->initRes ? array(
			'zoom/main.css' => new CSSFile(),
			'zoom/main.js' => new JSFile(),
		) : array();
	}
	
	/* activates zoom on preview action and LargeAlbumViewModule's large action */
	public function handleRequest(Request $request) {
		if ($request->scope == array('lonely') && (($request->action == array('preview') && preg_match('/\.(png|jpe?g|gif)$/i', $request->file)) || $request->action == array('large'))) {
			$this->initRes = true;
		}
		/* don't stop execution */
		return true;
	}
}

class CSSFile extends \LonelyGallery\CSSFile {
	
	public function whenModified() {
		return filemtime(__FILE__);
	}
	
	public function getContent() {
		return <<<'CSS'
body > #zoombox {
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
body > #zoombox img {
	position: relative;
	width: auto;
	height: auto;
}
body > #zoombox #zoombox-map {
	position: absolute;
	top: 10px;
	left: 10px;
	border: 1px solid #ccc;
	background-color: rgba(255, 255, 255, .1);
}
body > #zoombox #zoombox-map #zoombox-view {
	position: relative;
	background-color: rgba(255, 255, 255, .1);
	border: 1px solid #333;
}
a.zoombox-tr {
	position: absolute;
	top: 0;
	left: 40%;
	height: 100%;
	width: 20%;
	color: #fff;
	opacity: 0;
	transition: opacity 0.3s;
	text-shadow: #000 0px 0px 10px;
}
a.zoombox-tr:hover, a.zoombox-tr:focus {
	opacity: 1;
}
a.zoombox-tr:after {
    content: "+";
	display: block;
	line-height: 80px;
    font-size: 80px;
    margin-top: -40px;
    position: relative;
    top: 50%;
}
CSS;
	}
}

class JSFile extends \LonelyGallery\JSFile {
	
	public function whenModified() {
		return filemtime(__FILE__);
	}
	
	public function getContent() {
		return <<<'JS'
var zoom_img, zoom_div,
	zoom_map, zoom_view,
	zoom_map_height, zoom_map_width,
	zoom_map_view_height, zoom_map_view_width,
	zoom_map_move_height, zoom_map_move_width,
	aspect_ratio, width_aspect, height_aspect,
	body_overflow,
	zoom_map_area = 2500;

function initZoom() {
	var img = document.querySelectorAll('.file img.preview');
	for (var i = 0; i < img.length; ++i) {
		initImageZoom(img[i]);
	}
}

function initImageZoom(image) {
	if (window.innerHeight <= image.naturalHeight || window.innerWidth <= image.naturalWidth) {
		if (image.getAttribute('data-zoom-on') != '1') {
			var aZoom = document.createElement('a');
			aZoom.href = '#';
			aZoom.className = 'zoombox-tr';
			aZoom.onclick = function(img){
				return function(){
					
					zoom_div = document.createElement('div');
					zoom_div.id = 'zoombox';
					zoom_div.onclick = function(){
						window.removeEventListener('mousemove', zoomPos);
						window.removeEventListener('resize', zoomChangeMap);
						document.body.removeChild(zoom_div);
						document.body.style.overflowY = body_overflow;
					};
					
					zoom_img = document.createElement('img');
					zoom_img.src = img.src;
					zoom_map = document.createElement('div');
					zoom_map.id = 'zoombox-map';
					
					aspect_ratio = img.naturalWidth/img.naturalHeight;
					
					zoom_map_height = Math.round(Math.sqrt(zoom_map_area/aspect_ratio));
					zoom_map_width = Math.round(zoom_map_area/zoom_map_height);
					zoom_map.style.height = zoom_map_height+2 + 'px';
					zoom_map.style.width = zoom_map_width+2 + 'px';
					
					zoom_view = document.createElement('div');
					zoom_view.id = 'zoombox-view';
					
					zoom_map.appendChild(zoom_view);
					zoom_div.appendChild(zoom_img);
					zoom_div.appendChild(zoom_map);
					
					zoomChangeMap();
					zoomPos({clientX: window.innerWidth/2, clientY: window.innerHeight/2});
					document.body.appendChild(zoom_div);
					
					body_overflow = document.body.style.overflowY;
					document.body.style.overflowY = 'hidden';
					
					window.addEventListener('mousemove', zoomPos);
					window.addEventListener('resize', zoomChangeMap);
					
					return false;
				};
			}(image);
			image.parentNode.appendChild(aZoom);
			image.setAttribute('data-zoom-on', '1');
		}
	} else if (image.getAttribute('data-zoom-on') == '1') {
		var ztr = image.parentNode.querySelector('.zoombox-tr');
		ztr.parentNode.removeChild(ztr);
		image.setAttribute('data-zoom-on', '0');
	}
}

function zoomPos(event) {
	if (window.innerWidth <= zoom_img.naturalWidth) {
		var x = Math.min(1, (event.clientX - 200) / (window.innerWidth - 400));
		zoom_img.style.marginLeft = '-' + (x * (zoom_img.naturalWidth - window.innerWidth)) + 'px';
		zoom_view.style.marginLeft = Math.max(0, x * zoom_map_move_width) + 'px';
	}
	if (window.innerHeight <= zoom_img.naturalHeight) {
		var y = Math.min(1, (event.clientY - 200) / (window.innerHeight - 400));
		zoom_img.style.marginTop = '-' + (y * (zoom_img.naturalHeight - window.innerHeight)) + 'px';
		zoom_view.style.marginTop = Math.max(0, y * zoom_map_move_height) + 'px';
	} else {
		zoom_img.style.marginTop = '-' + zoom_img.naturalHeight/2 + 'px';
		zoom_img.style.top = '50%';
	}
}

function zoomChangeMap() {
	width_aspect = zoom_img.naturalWidth/window.innerWidth;
	height_aspect = zoom_img.naturalHeight/window.innerHeight;
	zoom_map_view_width = Math.round(Math.min(zoom_map_width, zoom_map_width/width_aspect));
	zoom_map_view_height = Math.round(Math.min(zoom_map_height, zoom_map_height/height_aspect));
	zoom_map_move_width = zoom_map_width - zoom_map_view_width;
	zoom_map_move_height = zoom_map_height - zoom_map_view_height;
	zoom_view.style.width = zoom_map_view_width + 'px';
	zoom_view.style.height = zoom_map_view_height + 'px';
}
window.addEventListener('resize', initZoom);
JS;
	}
}
?>