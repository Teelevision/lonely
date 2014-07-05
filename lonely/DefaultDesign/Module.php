<?php
/*
##########################
### Lonely PHP Gallery ###
##########################
## Default Design Module #
##########################
This file is part of the the Lonely Gallery.

### Version ###

1.1.0 dev
date: 2014-07-05

### License & Requirements & More ###

Copyright (c) 2014 Marius 'Teelevision' Neugebauer.
See LICENSE.txt, README.txt
and https://github.com/Teelevision/lonely

### Description ###

This is the default design.
It provides a black CSS theme and image size adjustment via JavaScript
to fit images into the screen if they exceed the height.
*/

namespace LonelyGallery\DefaultDesign;
use \LonelyGallery\Lonely as Lonely;

class Module extends \LonelyGallery\Design {
	
	/* returns settings for default design */
	public function afterConstruct() {
		Lonely::model()->jsfiles[] = Lonely::model()->configScript.'lonely.js';
		Lonely::model()->footer .= "<script type=\"text/javascript\">
var img = document.querySelectorAll('.file img.preview');
for (var i = 0; i < img.length; ++i) {
	adjustImageHeight(img[i]);
	img[i].addEventListener('load', function(image){
		return function(){
			adjustImageHeight(image);
		};
	}(img[i]));
}
</script>";
	}
	
	/* returns an array with css files to be loaded as design */
	public function cssFiles() {
		return array(Lonely::model()->configScript.'lonely.css');
	}
	
	/* config files */
	public function configAction(\LonelyGallery\Request $request) {
		if ($request->action[0] == 'lonely.css') {
			$this->displayCSS();
		} else if ($request->action[0] == 'lonely.js') {
			$this->displayJS();
		}
	}
	
	/* lonely.css */
	public function displayCSS() {
		
		$lastmodified = filemtime(__FILE__);
		if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE']) && $lastmodified && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $lastmodified) {
			header("HTTP/1.1 304 Not Modified", true, 304);
			exit;
		}
		
		header("Last-Modified: ".date(DATE_RFC1123, $lastmodified));
		header('Content-Type: text/css');
		?>
body {
    margin: 0;
	background-color: #111;
	color: #fff;
	font-family: Arial,Helvetica,sans-serif;
	font-size: 14px;
}
body > *:not(#content), #content > *:not(.file), .file > *:not(.preview-box) {
	margin-left: 8px;
	margin-right: 8px;
}
#content > .album {
	margin-right: 0;
}
#content > .album > *:not(.albums):not(.files) {
	margin-right: 8px;
}
a {
	color: #f20;
	text-decoration: none;
}
a:hover {
	color: #fff;
}
h1 {
	font-size: 26px;
}
h1 a {
	color: #fff;
}
#content > .album > .links, #content > .file > .links {
	margin: 0;
	padding: 0;
	position: absolute;
	top: 8px;
	right: 0;
}
#content > .album > .links > li, #content > .file > .links > li {
	display: inline;
}
#content > .album > .links > li:after, #content > .file > .links > li:after {
	content: " | ";
}
#content > .album > .links > li:first-child:before, #content > .file > .links > li:first-child:before {
	content: "[ ";
}
#content > .album > .links > li:last-child:after, #content > .file > .links > li:last-child:after {
	content: " ]";
}
ul.breadcrumbs > li {
	display: inline;
}
ul.breadcrumbs > li:not(:first-child):before {
	content: " >> ";
}
.album > .album-text {
	margin: 16px 0;
}
.album > .albums, .album > .files {
	overflow: auto;
	padding: 0;
	margin: 8px 0;
}
.album > .albums > li, .album > .files > li {
	position: relative;
	display: block;
	float: left;
	width: 146px;
	height: 146px;
	overflow: hidden;
	background-color: #000;
	text-align: center;
	line-height: 125px;
	margin: 0 8px 8px 0;
}
.album > .files > li {
	width: 300px;
	height: 300px;
	line-height: 280px;
}
.album > .files > li img.thumb {
	height: 300px;
	width: 300px;
}
.album > .albums > li img.thumb {
	height: 146px;
	width: 146px;
}
.album > .albums > li > a.thumb-link, .album > .files > li > a.thumb-link {
	color: #fff;
	position: absolute;
	top: 0;
	left: 0;
	width: 126px;
	height: 126px;
	padding: 10px;
	background-color: rgba(0,0,0,0);
	transition: background-color 0.3s;
}
.album > .files > li > a.thumb-link {
	width: 280px;
	height: 280px;
	background-color: rgba(0,0,0,.4);
	opacity: 0;
	transition: opacity 0.3s;
}
.album > .albums > li > a.thumb-link:hover, .album > .albums > li > a.thumb-link:focus {
	background-color: rgba(0,0,0,.4);
}
.album > .files > li > a.thumb-link:hover, .album > .files > li > a.thumb-link:focus {
	opacity: 1;
}
.album > .albums > li > a.thumb-link span, .album > .files > li > a.thumb-link span {
	background-color: #111;
	display: inline-block;
	line-height: 150%;
	padding: 4px 8px;
	box-shadow: 0 0 2px #111;
	vertical-align: middle;
	word-wrap: break-word;
	max-width: 110px;
}
.album > .files > li > a.thumb-link span {
	max-width: 264px;
}
.file > header .nav, .file .title, .file .download {
	text-align: center;
}
.file > header .breadcrumbs {
	margin-bottom: 0;
}
.file > header .nav {
	margin: 0;
}
.file > header .nav * {
	display: inline-block;
	line-height: 400%;
}
.file > header .nav-first:before { content: "<< "; }
.file > header .nav-prev:before { content: "< "; }
.file > header .nav-album:before { content: "["; }
.file > header .nav-album:after { content: "]"; }
.file > header .nav-next:after { content: " >"; }
.file > header .nav-last:after { content: " >>"; }
.file .preview {
	max-width: 100%;
	display: inline-block;
	margin: 0 auto;
	vertical-align: middle;
}
.file .preview-box {
	position: relative;
	text-align: center;
	min-height: 200px;
	line-height: 200px;
	overflow: hidden;
}
.file .preview-box .nav {
	position: absolute;
	top: 0;
	left: 0;
	height: 100%;
	width: 40%;
	color: #fff;
	opacity: 0;
	transition: opacity 0.3s;
	text-shadow: #000 0px 0px 10px;
}
.file .preview-box .nav:hover, .file .preview-box .nav:focus {
	opacity: 1;
}
.file .preview-box .nav.next {
	right: 0;
	left: auto;
}
.file .preview-box .nav.prev:before, .file .preview-box .nav.next:after {
	content: "<";
	display: block;
	line-height: 80px;
	font-size: 80px;
	margin-top: -40px;
	position: relative;
	top: 50%;
}
.file .preview-box .nav.next:after {
	content: ">";
}
.file .preview-box .preview-controls-sideways {
	max-width: 700px;
	display: inline-block;
}
.file .preview-box .preview-controls-sideways ~ a.prev {
	left: 50%;
	margin-left: -710px;
	width: 350px;
}
.file .preview-box .preview-controls-sideways ~ a.next {
	left: 50%;
	margin-left: 360px;
	width: 350px;
}
.file .preview-box .preview-controls-sideways ~ a.prev:before {
	text-align: right;
}
.file .preview-box .preview-controls-sideways ~ a.next:after {
	text-align: left;
}
.file .info p, .file .info dl {
    margin: 4px 0;
}
.file .title {
    font-size: 16px;
	color: #fff;
}
.file .title:before, .file .title:after {
    content: "» ";
	color: #666;
	font-size: 24px;
}
.file .title:after {
    content: " «";
}
<?php
		exit;
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
function adjustMaxImageHeight() {
	var img = document.querySelectorAll(".file img.preview");
	for (var i = 0; i < img.length; ++i) {
		adjustImageHeight(img[i]);
	}
}
function adjustImageHeight(image) {
	image.style.maxHeight = window.innerHeight + 'px';
}
function navigate(event) {
	var k = event.keyCode;
	switch (k) {
		case 37: // left arrow
		case 39: // right arrow
			var a = document.querySelector(".file .nav a[rel='next']");
			if (a) {
				window.location = a.href;
				return false;
			}
			break;
	}
}

window.addEventListener('resize', adjustMaxImageHeight);
window.addEventListener('keydown', navigate);
<?php
		exit;
	}
}
?>