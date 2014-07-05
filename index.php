<?php
/*
##########################
### Lonely PHP Gallery ###
##########################

### Version ###

1.1.0 dev version
date: 2013-12-20

### Requirements ###

PHP 5.3.0 or above
GD library for PHP

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

Lonely PHP Gallery is a image gallery that aims to be simple in many 
ways: Its functionality is limited to the basics you need to properly 
display and navigate through your images and can be extended by modules 
if needed. Its design is plain - the focus should lie on your images. 
Its implementation uses widely supported and up to date standards like 
HTML5 and CSS3. Its configuration is based on text files and does not 
require any PHP knowledge.

### Installation ###

Place the index.php in a directory of your webspace that contains 
images.

### How it works ###

By default images of the types JPG, PNG and GIF will be displayed in the 
gallery. Directories are taken as albums and can be nested. Small 
thumbnails of your images and albums will be rendered.

### The config and thumb directories ###

When calling your gallery it creates a 'thumb' directory containing 
rendered thumbnail. It also creates an empty 'config' directory where 
you can modify the gallery. Deleting the thumb directory causes all 
thumbnails to be re-rendered which might be neccessary some time. Note 
that your browser probably caches the thumbnails. In some browsers you 
can force a reload by pressing Ctrl+F5. Try both if you have trouble 
with wrong thumbnails.

### Configuration ###

You can make settings by creating text files in the config directory. 
There are a few different types of settings, where 'name' is a 
placeholder for the actual setting name:
 * name.txt: the value of 'name' is the content of this file
 * names.list.txt: each line of the file is one value of the list 'names'
 * name: turn 'name' on
 * -name: turn 'name' off

For example for changing the title of your gallery, add a 'title.txt' to 
your config directory. Adjust the content of this file with a plain text 
editor like notepad.exe on Windows, TextEdit on Mac (use Format > Make 
Plain Text) or gedit on Linux.

Settings:
name             | file name            | default
    description
-----------------------------------------------------------------------------
title            | title.txt            | Lonely Gallery
    the title of your gallery
description      | description.txt      | 
    very short description of the website; invisible metadata used by
    search engines
keywords         | keywords.txt         | 
    keywords about the website; invisible metadata used by search engines
author           | author.txt           | 
    name of the website's author; invisible metadata used by search engines
robots           | robots.txt           | 
    directive for search engines; 'noindex,nofollow' will tell a search
	engine not to index the website; leave blank to be indexed
footer           | footer.txt           | 
    text that is shown at the bottom of the gallery; you may put a legal
	notice here; you can use html
useOriginals     | useOriginals         | off
    always use full size images instead of 700px rendered versions; use
	only if you resize your images before adding them to the gallery,
	otherwise they generate a lot of traffic
albumThumbSquare | albumThumbSquare.txt | 2
    the number of images used in an album thumbnail is the square of this;
	setting this to 2 results in 4 images, 3 in 9 and 4 in 16
shortUrls        | shortUrls            | off
    use fance short urls like /foo/bar instead of /index.php?/foo/bar;
	only works if your webserver rewrites these urls to the old ones;
	for nginx this might work:
	if (!-f $request_filename) {
		rewrite ^(.+)$ /index.php?$1 last;
	}

### Modules and designs ###

Modules are PHP files that can simply be placed in the 'config' 
directory. They will work right away. Settings of a module can be set 
like every other setting within the 'config' directory. You can 
deactivate a module by prepeding the filename with a minus, e.g. 
'-ExampleModule.php'. You can deactivate a module in a aub directory by 
adding a file named like '-ExampleModule' to the config directory.
Designs are basicly modules.

### Album thumbnails ###

Album thumbnails are rendered with the first 4 files of a directory by 
default. You can change the number by setting albumThumbSquare. You can 
add '_thumb.jpg' or '_thumb.png' files to albums to make it the album's
thumbnail. You can add a text file named '_thumb.txt' to the album to 
define which image is used as the album's thumbnail. If you define 
several files within the '_thumb.txt', one per line, they are all taken 
into the thumbnail. Free spots are filled up with images from the album.
You can state images in '_thumb.txt' like this:
 * foo.jpg: 'foo.jpg' in the current directory
 * foo/bar.jpg: file named 'bar.jpg' in the sub directory 'foo'
 * ../foo.jpg: 'foo.jpg' in the parent directory
 * /bar.jpg: 'bar.jpg' in the root directory of the gallery

### Album text ###

By adding a file called '_text.txt' to a directory its content will be 
displayed at the top of the album page. Html is possible.

### Redirect album ###

By placing a file called '_redirect.txt' in a directory it will redirect
you to another album. Just write the path of the other album into the
file like for album thumbnails (see above).

### Hidden files ###

Files beginning with a dot (.), a minus (-) or an underscore (_) are not
displayed in the gallery. You can still refer to hidden files in a
'_thumb.txt' file.

### PHP memory_limit ###

PHP's memory_limit parameter can break the rendering of bigger files if 
set to low. Here is a table showing the relation between memory_limit 
and megapixels. This table is the result of a short test and might be 
wrong.

memory_limit | megapixels
         16M | 2
         32M | 5
         64M | 10
        512M | 50
       1024M | 100

I recommend setting memory_limit to 64M if you are using a digital 
camera up to 10 megapixels and do not resize manually.
*/

error_reporting(E_ALL ^ E_NOTICE ^ E_STRICT);

/* bootstrap */

if (!defined('LONELY_ONEFILE')) {
	require(__DIR__.'/lonely/Lonely.php');
}

/* aaand ... action! */
\LonelyGallery\Lonely::model()->run(__DIR__, array());

?>