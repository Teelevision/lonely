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
	
	/* returns an array with config-relative web paths to ResourceFile instances */
	public function resources() {
		return array(
			'lonely.css' => new CSSFile(),
			'lonely.js' => new JSFile(),
		);
	}
}

class CSSFile extends \LonelyGallery\CSSFile {
	
	public function whenModified() {
		return filemtime(__FILE__);
	}
	
	public function getContent() {
		return <<<'CSS'
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
.file {
	padding-bottom: 50px;
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
	width: 146px;
	height: 146px;
	padding: 10px;
	background-color: rgba(0,0,0,0);
	transition: background-color 0.3s;
}
.album > .albums > li > a.thumb-link {
	padding: 0;
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
}
.album > .albums > li > a.thumb-link span {
	position: absolute;
	bottom: 0;
	left: 0;
	width: 130px;
	background-color: rgba(17,17,17,.7);
	box-shadow: 0 0 2px rgba(17,17,17,.7);
	transition: background-color 0.3s, box-shadow 0.3s;
}
.album > .albums > li > a.thumb-link:hover span, .album > .albums > li > a.thumb-link:focus span {
	background-color: rgba(17,17,17,1);
	box-shadow: 0 0 2px rgba(17,17,17,1);
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
body > #keynavinfo {
	width: 300px;
	margin-left: -150px;
	left: 50%;
	position: fixed;
	top: 20px;
	background-color: #111;
	border: 1px solid yellow;
	border-radius: 4px;
}
body > #keynavinfo p {
	color: white;
	margin: 10px;
}
body > #keynavinfo p .keynavinfo-key {
	color: yellow;
	font-weight: bold;
	padding: 0 2px;
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
function adjustMaxImageHeight() {
	var img = document.querySelectorAll(".file img.preview");
	for (var i = 0; i < img.length; ++i) {
		adjustImageHeight(img[i]);
	}
}
function adjustImageHeight(image) {
	image.style.maxHeight = window.innerHeight + 'px';
}
var keyNavInfoDiv = null;
function showKeyNavigation() {
	if (keyNavInfoDiv == null) {
		keyNavInfoDiv = document.createElement('div');
		keyNavInfoDiv.id = 'keynavinfo';
		var p = document.createElement('p');
		
		var keys = { '1': '1', '9': '9', '0': '0', a: 'A', d: 'D', left: 'left', right: 'right', w: 'W', h: 'H', i: 'I' };
		for (var k in keys) {
			var s = document.createElement('span');
			s.appendChild(document.createTextNode(keys[k]));
			s.className = 'keynavinfo-key';
			keys[k] = s;
		}
		
		p.appendChild(document.createTextNode('You can use the numbers on the numpad to go to the first ten images ('));
		p.appendChild(keys['1']);
		p.appendChild(document.createTextNode(', ..., '));
		p.appendChild(keys['9']);
		p.appendChild(document.createTextNode(', '));
		p.appendChild(keys['0']);
		p.appendChild(document.createTextNode('). Use the regular numbers to do the same with albums. Skip to the previous/next image using the '));
		p.appendChild(keys['a']);
		p.appendChild(document.createTextNode('/'));
		p.appendChild(keys['d']);
		p.appendChild(document.createTextNode(' or the '));
		p.appendChild(keys['left']);
		p.appendChild(document.createTextNode('/'));
		p.appendChild(keys['right']);
		p.appendChild(document.createTextNode(' arrow keys. Press '));
		p.appendChild(keys['w']);
		p.appendChild(document.createTextNode(' to go an album up. '));
		p.appendChild(keys['h']);
		p.appendChild(document.createTextNode(' brings you back to the home. '));
		p.appendChild(keys['i']);
		p.appendChild(document.createTextNode(' toggles this info.'));
		
		keyNavInfoDiv.appendChild(p);
		document.body.appendChild(keyNavInfoDiv);
	} else {
		document.body.removeChild(keyNavInfoDiv);
		keyNavInfoDiv = null;
	}
}
function navigate(event) {
	var k = event.keyCode;
	var a = false;
	switch (k) {
		case 37: // left arrow
		case 65: // a
			a = document.querySelector(".file a.nav.prev"); break;
		case 39: // right arrow
		case 68: // d
			a = document.querySelector(".file a.nav.next"); break;
		case 87: // w
			a = document.querySelector(".breadcrumbs li:nth-last-child(2) a"); break;
		case 72: // h
			a = document.querySelector("h1 a"); break;
		case 49: // 1
			a = document.querySelector(".albums > *:nth-child(1) a.thumb-link"); break;
		case 50: // 2
			a = document.querySelector(".albums > *:nth-child(2) a.thumb-link"); break;
		case 51: // 3
			a = document.querySelector(".albums > *:nth-child(3) a.thumb-link"); break;
		case 52: // 4
			a = document.querySelector(".albums > *:nth-child(4) a.thumb-link"); break;
		case 53: // 5
			a = document.querySelector(".albums > *:nth-child(5) a.thumb-link"); break;
		case 54: // 6
			a = document.querySelector(".albums > *:nth-child(6) a.thumb-link"); break;
		case 55: // 7
			a = document.querySelector(".albums > *:nth-child(7) a.thumb-link"); break;
		case 56: // 8
			a = document.querySelector(".albums > *:nth-child(8) a.thumb-link"); break;
		case 57: // 9
			a = document.querySelector(".albums > *:nth-child(9) a.thumb-link"); break;
		case 48: // 0
			a = document.querySelector(".albums > *:nth-child(10) a.thumb-link"); break;
		case 97: // 1 on numblock
			a = document.querySelector(".files > *:nth-child(1) a.thumb-link"); break;
		case 98: // 2 on numblock
			a = document.querySelector(".files > *:nth-child(2) a.thumb-link"); break;
		case 99: // 3 on numblock
			a = document.querySelector(".files > *:nth-child(3) a.thumb-link"); break;
		case 100: // 4 on numblock
			a = document.querySelector(".files > *:nth-child(4) a.thumb-link"); break;
		case 101: // 5 on numblock
			a = document.querySelector(".files > *:nth-child(5) a.thumb-link"); break;
		case 102: // 6 on numblock
			a = document.querySelector(".files > *:nth-child(6) a.thumb-link"); break;
		case 103: // 7 on numblock
			a = document.querySelector(".files > *:nth-child(7) a.thumb-link"); break;
		case 104: // 8 on numblock
			a = document.querySelector(".files > *:nth-child(8) a.thumb-link"); break;
		case 105: // 9 on numblock
			a = document.querySelector(".files > *:nth-child(9) a.thumb-link"); break;
		case 96: // 0 on numblock
			a = document.querySelector(".files > *:nth-child(10) a.thumb-link"); break;
		case 73: // i
			showKeyNavigation(); break;
	}
	if (a) {
		window.location = a.href;
		return false;
	}
}

window.addEventListener('resize', adjustMaxImageHeight);
window.addEventListener('keydown', navigate);
JS;
	}
}
?>