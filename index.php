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

Settings can be overwritten in albums by creating a 'config' directory 
in the album directory and place the setting file in it. Not every 
setting should be overwritten.

Settings:
name             | file name            | default        | overwritable
    description
-----------------------------------------------------------------------------
title            | title.txt            | Lonely Gallery | better not
    the title of your gallery
description      | description.txt      |                | better not
    very short description of the website; invisible metadata used by
    search engines
keywords         | keywords.txt         |                | better not
    keywords about the website; invisible metadata used by search engines
author           | author.txt           |                | better not
    name of the website's author; invisible metadata used by search engines
robots           | robots.txt           |                | better not
    directive for search engines; 'noindex,nofollow' will tell a search
	engine not to index the website; leave blank to be indexed
footer           | footer.txt           |                | yes
    text that is shown at the bottom of the gallery; you may put a legal
	notice here; you can use html
useOriginals     | useOriginals         | off            | yes
    always use full size images instead of 700px rendered versions; use
	only if you resize your images before adding them to the gallery,
	otherwise they generate a lot of traffic
albumThumbSquare | albumThumbSquare.txt | 2              | yes
    the number of images used in an album thumbnail is the square of this;
	setting this to 2 results in 4 images, 3 in 9 and 4 in 16
shortUrls        | shortUrls            | off            | no
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
add a JPG file named '_thumb.jpg' to an album to make it the album's 
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

namespace LonelyGallery;

error_reporting(E_ALL ^ E_NOTICE ^ E_STRICT);

/* aaand ... action! */
Lonely::model()->run();


/* base class for all lonely classes */
abstract class Component {
	
	protected $data = array();
	
	/*
	property overloading
	On setting/getting of a property the setter/getter method is called.
	If there is no such method, it will be stored to / fetched from the $data property.
	Getters/setters start with get/set followed by one upper case letter, then only lower case.
	Example:
			$this->ABContent = 123;
		will call the method setAbcontent() if defined or it is stored as $data['abcontent'].
	*/
	
	public function __isset($name) {
		$name = strtolower($name);
		$method = 'get'.ucfirst($name);
		return (method_exists($this, $method) || isset($this->data[$name]));
	}
	
	public function __get($name) {
		$name = strtolower($name);
		$method = 'get'.ucfirst($name);
		if (method_exists($this, $method)) {
			return call_user_func(array($this, $method));
		}
		return isset($this->data[$name]) ? $this->data[$name] : null;
	}
	
	public function __set($name, $value) {
		$name = strtolower($name);
		$method = 'set'.ucfirst($name);
		if (method_exists($this, $method)) {
			return call_user_func(array($this, $method), $value);
		}
		$this->data[$name] = $value;
	}
	
	public function __unset($name) {
		$name = strtolower($name);
		$method = 'unset'.ucfirst($name);
		if (method_exists($this, $method)) {
			return call_user_func(array($this, $method), $value);
		}
		if (isset($this->data[$name])) {
			unset($this->data[$name]);
		}
	}
}

class Request extends Component {
	
	/*
	Request: /<scope>/<album>/<file>/<action>
	Each element is optional.
	While album and file refers to a real existing file or directory in the gallery, the scope can be a virtual directory. Additional scopes could be useful to implement new pages that refer to files or albums like slideshows or a shop system, or even file unrelated, static pages.
	The action is to provide several actions to one set of scope, album and file. You could use actions to provide e.g. a comment section or. Everything you could provide by scope you can also provide by action. The main difference is that scopes are matched befor album/file and actions after. Since you should take care that scope and action names don't collide with albums and files, it is mainly a design question whether to use scopes or actions.
	If the album/file is not recognized as a part of the gallery, it is matched as action.
	Examples:
		/thumb/300px/Holiday/2013/01.jpg
			scope: thumb/300px
			album: Holiday/2013
			file: 01.jpg
			action: index
		/ABC.png/comments/new
			scope: lonely
			album (empty)
			file: ABC.png
			action: /comments/new
	*/
	
	const MATCH_STRING = 0;
	const MATCH_REGEX = 1;
	
	/* scope, defaults to 'lonely' */
	private $scope = array('lonely');
	/* album, defaults to none */
	private $album = array();
	/* file, defaults to none */
	private $file = '';
	/* action, defaults to 'index' */
	private $action = array('index');
	
	
	function __construct($scopePatterns) {
		
		/* get request string */
		if (isset($_SERVER['QUERY_STRING'])) {
			$request = rawurldecode($_SERVER['QUERY_STRING']);
		} else {
			$request = rawurldecode($_SERVER['REQUEST_URI']);
			if (strpos($request, $_SERVER['SCRIPT_NAME']) === 0) {
				/* cut off path and filename of this script */
				$request = substr($request, 1 + strlen($_SERVER['SCRIPT_NAME']));
			} else {
				/* cut off path of this script */
				$request = substr($request, 1 + strlen(dirname($_SERVER['SCRIPT_NAME'])));
			}
		}
		
		/* convert to array and remove empty entries, then rebuild keys */
		$requestArray = array_values(array_diff(explode('/', $request), array('')));
		
		/* match scope */
		if (preg_match_any((array)$scopePatterns, implode('/', $requestArray), $match)) {
			$this->scope = explode('/', $match[1]);
			$requestArray = array_slice($requestArray, count($this->scope));
		}
		
		/* match album, file and action */
		/* search for the longest path that is a file or dir */
		$num = count($requestArray);
		for ($i = 0; $i <= $num; ++$i) {
			
			$path = __DIR__.DIRECTORY_SEPARATOR.implode(DIRECTORY_SEPARATOR, array_slice($requestArray, 0, $num - $i));
			
			/* check if file */
			if (@is_file($path)) {
				$pos = max(0, $num - $i - 1);
				$this->album = array_slice($requestArray, 0, $pos);
				$this->file = $requestArray[$pos];
				if ($num > ($pos + 1)) {
					$this->action = array_slice($requestArray, $pos + 1);
				}
				break;
			}
			if (@is_dir($path)) {
				$pos = $num - $i;
				$this->album = array_slice($requestArray, 0, $pos);
				if ($num > $pos) {
					$this->action = array_slice($requestArray, $pos);
				}
				break;
			}
			
		}
	}
	
	public function moveFileToAction() {
		if ($this->file !== '') {
			$this->action = $this->action == array('index') ? array($this->file) : array_merge($this->action, array($this->file));
			$this->file = '';
		}
	}
	
	public function getScope() {
		return $this->scope;
	}
	
	public function getAlbum() {
		return $this->album;
	}
	
	public function getFile() {
		return $this->file;
	}
	
	public function getAction() {
		return $this->action;
	}
}

class Lonely extends Component {
	
	/* gallery title */
	public $title = 'Lonely Gallery';
	
	/* META data */
	public $description = '';
	public $keywords = '';
	public $author = '';
	public $robots = '';
	
	/* HTML to be displayed at the end of the page */
	public $footer = '';
	
	/* name of the thumbnail sub directory */
	public $thumbDirectory = 'thumb';
	
	/* name of the config sub directory */
	public $configDirectory = 'config';
	
	/* name of the thumb file of albums */
	public $albumThumb = '_thumb.jpg';
	
	/* file containing the name of the thumb file of an album */
	public $albumThumbFile = '_thumb.txt';
	
	/* file containing text/html to display at top of an album */
	public $albumText = '_text.txt';
	
	/* album class to use */
	public $albumClass = 'Album';
	
	/* class namespace of the default design which is used if there is no design module */
	public $defaultDesign = 'DefaultDesign';
	
	/* default file action */
	public $defaultFileAction = 'preview';
	
	/* whether to use the original file rather than a rendered version on a preview page */
	public $useOriginals = false;
	
	/* the number of images in an album thumbnail is the square of this value, e.g. "2" will result in 2x2=4 images */
	public $albumThumbSquare = 2;
	
	/* omit the script in the url: instead of "/index.php?/foo", urls will look like "/foo"; works only if your webserver rewrites these urls right */
	public $shortUrls = false;
	
	/* css and javascript files to be loaded */
	public $cssfiles = array();
	public $jsfiles = array();
	
	/* hidden elements */
	public $hiddenNames = array('/^($|\.|-|_)/');
	public $hiddenFileNames = array();
	public $hiddenAlbumNames = array();
	
	/* modules */
	private $_modules = array();
	
	/* design */
	private $_design = null;
	
	/* files */
	private $_files = array();
	
	/* singleton model */
	protected static $_model;
	
	/* singleton */
	private function __construct() {}
	private function __clone() {}
	private function __wakeup() {}
	
	public static function model() {
		if (self::$_model === null) {
			self::$_model = new self;
		}
		return self::$_model;
	}
	
	public function run(Array $settings = array()) {
		
		/* initial settings */
		$this->set($settings);
		
		/* set start time to mesure execution time */
		$this->startTime = microtime(true);
		
		/*
		Path variables (sheme: [prefix][Suffix], eg. rootDir)
			prefixes:
				root: top of the gallery and where the images are located
				thumb: where the thumbnails are
				config: where the configuration is
			suffixes:
				Dir: absolute local location
				Path: root-relative web location
				Script: root-relative web location containing the script
		Examples:
			thumbPath: web path to the thumbnail directory
			thumbScript: web path to this script to create/update and then show the thumbnail
			configDir: read files of modules, etc.
		*/
		
		/* config directory */
		$this->rootDir = __DIR__.DIRECTORY_SEPARATOR;
		
		/* set default design */
		if ($this->defaultDesign) {
			$this->addModule($this->defaultDesign);
		}
		
		/* check for GD */
		if (!function_exists('gd_info')) {
			$this->error(500, 'Missing GD library. Make sure your PHP installation includes the GD library.');
		}
		
		/* config directory */
		$this->configDir = $this->rootDir.$this->configDirectory.DIRECTORY_SEPARATOR;
		if (!is_dir($this->configDir)) {
			if (!mkdir($this->configDir)) {
				$this->error(500, 'Config directory (/'.$this->configDirectory.') could not be created. Check if your user has permission to write to your gallery directory.');
			}
		}
		
		/* read data from config dir */
		if (is_readable($this->configDir) && is_executable($this->configDir)) {
			$this->readConfig($this->configDir);
		} else {
			$this->error(500, 'Config directory (/'.$this->configDirectory.') is missing some rights.');
		}
		
		/* render directory */
		$this->thumbDir = $this->rootDir.$this->thumbDirectory.DIRECTORY_SEPARATOR;
		if (!is_dir($this->thumbDir)) {
			if (!mkdir($this->thumbDir)) {
				$this->error(500, 'Thumbnail directory (/'.$this->thumbDirectory.') could not be created. Check if your user has permission to write to your gallery directory.');
			}
			if (!is_readable($this->thumbDir)) {
				$this->error(500, 'Thumbnail directory (/'.$this->thumbDirectory.') is not readable.');
			}
		}
		
		/* the gallery's full URL */
		$this->server = 'http'.(empty($_SERVER['HTTPS']) ? '' : 's').'://' // scheme
				.$_SERVER['SERVER_NAME'] // domain
				.((($_SERVER['SERVER_PORT'] == 80 && empty($_SERVER['HTTPS'])) || ($_SERVER['SERVER_PORT'] == 443 && !empty($_SERVER['HTTPS']))) ? '' : ':'.$_SERVER['SERVER_PORT']); // port
		/* root-relative path */
		$this->rootPath = dirname($_SERVER['SCRIPT_NAME']);
		$this->rootPath .= ($this->rootPath == '/') ? '' : '/';
		$this->realRootScript = $_SERVER['SCRIPT_NAME'].'?/';
		$this->realRootScriptClean = $_SERVER['SCRIPT_NAME'];
		$this->rootScript = $this->shortUrls ? $this->rootPath : $this->realRootScript;
		$this->rootScriptClean = $this->shortUrls ? $this->rootPath : $this->realRootScriptClean;
		$this->thumbPath = $this->rootPath.$this->thumbDirectory.'/';
		$this->thumbScript = $this->realRootScript.$this->thumbDirectory.'/';
		$this->configPath = $this->rootPath.$this->configDirectory.'/';
		$this->configScript = $this->realRootScript.$this->configDirectory.'/';
		
		/* hidden files */
		$this->hiddenFileNames[] = '/^('.preg_quote($this->albumThumb).'|'.preg_quote($this->albumThumbFile).'|'.preg_quote($this->albumText).')$/i';
		$this->hiddenAlbumNames[] = '/^('.preg_quote($this->configDirectory).'|'.preg_quote($this->thumbDirectory).')$/i';
		
		/* init request */
		$scopePattern = '#^('.preg_quote($this->configDirectory).'|'.preg_quote($this->thumbDirectory).'/[0-9]+(px|sq))(/|$)#';
		$this->request = new Request($scopePattern);
		$album = $this->request->album;
		
		/* read data from album config dir */
		$num = count($album);
		for ($n = 1; $n <= $num; ++$n) {
			$dir = $this->rootDir.implode(DIRECTORY_SEPARATOR, array_slice($album, 0, $n)).DIRECTORY_SEPARATOR.$this->configDirectory.DIRECTORY_SEPARATOR;
			if (is_readable($dir) && is_executable($dir)) {
				$this->readConfig($dir);
			}
		}
		
		/* initialize modules */
		$this->initModules();
		
		/* init default files */
		$this->registerFileClass('\\LonelyGallery\\Image');
		if ($this->extensions) {
			$this->registerFileClass('\\LonelyGallery\\GenericFile');
		}
		
		/* check for hidden files and directories */
		if ($this->request->scope[0] != $this->thumbDirectory) {
			$file = $this->request->file;
			if ($file && $this->isHiddenFileName($file)) {
				$this->request->moveFileToAction();
			}
		}
		foreach ($album as $a) {
			if ($a && $this->isHiddenAlbumName($a)) {
				$this->error();
			}
		}
		
		/* build the method to call */
		$this->handleRequest($this->request);
		
	}
	
	public function set(Array $settings) {
		foreach ($settings as $name => $value) {
			$this->{$name} = $value;
		}
	}
	
	/* reads the files in the config directory */
	private function readConfig($dir) {
		foreach (scandir($dir) as $file) {
			
			/* skip hidden (./../.htaccess) */
			if ($file[0] == '.' || !is_file($dir.$file)) {
				continue;
			}
			
			/* turn off setting */
			else if ($file[0] == '-') {
				if (substr($file, -6) == "Module") {
					$this->removeModule(substr($file, 1));
				} else {
					$this->{substr($file, 1)} = false;
				}
			}
			
			/* modules always end on 'Module.php' and designs are modules ending on 'Design.php' */
			else if (($s = substr($file, -10)) == 'Module.php' || $s == 'Design.php') {
				$this->addModule(substr($file, 0, -4));
			}
			
			/* value */
			else if (substr($file, -4) == ".txt") {
				/* list */
				if (substr($file, -9, -4) == ".list") {
					$value = array();
					foreach (file($dir.$file, FILE_SKIP_EMPTY_LINES|FILE_IGNORE_NEW_LINES) as $line) {
						$value[] = self::utf8ify($line);
					}
					$this->{substr($file, 0, -9)} = $value;
				}
				/* single */
				else {
					$value = file_get_contents($dir.$file);
					$value = self::utf8ify($value);
					$this->{substr($file, 0, -4)} = trim($value);
				}
			}
			
			/* otherwise it is a turn on setting */
			else {
				$this->{$file} = true;
			}
			
		}
	}
	
	/* handle request */
	public function handleRequest(Request $request) {
		
		/* let modules interrupt the request */
		foreach ($this->_modules as $module) {
			if (method_exists($module, 'checkAccess') && !call_user_func(array($module, 'checkAccess'), $request)) {
				$this->error(403, 'You are not allowed to access this page.');
			}
		}
		
		/* let modules handle the request */
		foreach ($this->_modules as $module) {
			if (method_exists($module, 'handleRequest') && !call_user_func(array($module, 'handleRequest'), $request)) {
				return;
			}
		}
		
		$scope = $request->scope;
		$action = $request->action;
		
		switch ($scope[0]) {
			
			/* thumb */
			case $this->thumbDirectory:
			/* config */
			case $this->configDirectory:
				$method = $scope[0];
				foreach (array_slice($scope, 1) as $scope) {
					$method .= ucfirst($scope);
				}
				$method .= 'Action';
				break;
			
			/* lonely */
			case 'lonely':
			default:
				/* don't display album files */
				if ($request->file == $this->albumThumb) {
					$this->error();
				}
				$method = $scope[0];
				foreach (array_slice($scope, 1) as $scope) {
					$method .= ucfirst($scope);
				}
				$method .= ucfirst(preg_replace('#[^-\w\d]#', '_', $action[0])).'Action';
			
		}
		
		/* bring the action */
		$somethingCalled = false;
		if (method_exists($this, $method)) {
			call_user_func(array($this, $method), $this->request);
			$somethingCalled = true;
		}
		foreach ($this->_modules as $module) {
			if (method_exists($module, $method)) {
				call_user_func(array($module, $method), $this->request);
				$somethingCalled = true;
			}
		}
		
		/* nothing called */
		if (!$somethingCalled) {
			$this->error();
		}
	}
	
	/* evaluates if the file or dir name is hidden */
	public function isHiddenName($name) {
		return preg_match_any($this->hiddenNames, $name);
	}
	
	/* evaluates if the file name is hidden */
	public function isHiddenFileName($name) {
		return $name == preg_match_any($this->hiddenFileNames, $name) || $this->isHiddenName($name);
	}
	
	/* evaluates if the dir name is hidden */
	public function isHiddenAlbumName($name) {
		return $name == preg_match_any($this->hiddenAlbumNames, $name) || $this->isHiddenName($name);
	}
	
	/* add module */
	public function addModule($module) {
		$this->_modules[$module] = null;
	}
	
	/* remove module */
	public function removeModule($module) {
		if (array_key_exists($module, $this->_modules)) {
			unset($this->_modules[$module]);
		}
	}
	
	/* returns the list of modules */
	public function getModules() {
		return $this->_modules;
	}
	
	/* initializes the modules */
	public function initModules() {
		
		/* first load all files to prevent missing requirements */
		foreach ($this->_modules as $name => &$module) {
			if (!class_exists('\\LonelyGallery\\'.$name.'\\Module')) {
				require($this->configDir.$name.'.php');
			}
		}
		
		/* init objects */
		$fileClasses = array();
		foreach ($this->_modules as $name => &$module) {
			
			/* initialize module */
			$classname = '\\LonelyGallery\\'.$name.'\\Module';
			$module = new $classname();
			
			/* fetch settings from module */
			$this->set($module->settings());
			
			/* file classes */
			foreach ($module->fileClasses() as $fileclass => $prio) {
				$fileClasses['\\LonelyGallery\\'.$name.'\\'.$fileclass] = $prio;
			}
			
			/* switch designs */
			if ($module instanceof Design) {
				$this->_design = $module;
			}
			
		}
		
		/* init file objects */
		arsort($fileClasses, SORT_NUMERIC);
		foreach ($fileClasses as $fileclass => $prio) {
			$this->registerFileClass($fileclass);
		}
		
		$this->_modulesInitialized = true;
		
	}
	
	/* map file patterns to class */
	public function registerFileClass($classname) {
		$this->_files[$classname::pattern()] = $classname;
	}
	
	/* returns map from name pattern to file class */
	public function getFilePatterns() {
		return $this->_files;
	}
	
	/* calls an event */
	public function callEvent() {
		if (func_num_args() >= 1) {
			$args = func_get_args();
			$method = $args[0].'Event';
			$data = array();
			foreach ($this->_modules as $moduleName => $module) {
				if (method_exists($module, $method)) {
					$data[$moduleName] = call_user_func_array(array($module, $method), array_slice($args, 1));
				}
			}
			return $data;
		}
	}
	
	/* show album or image */
	protected function lonelyIndexAction(Request $request) {
		
		$album = Factory::createAlbum($request->album);
		$file = Factory::createFile($request->file, $album);
		
		/* file requested */
		if ($file && $file->isAvailable()) {
			
			/* redirect to the file */
			header('Location: '.$this->server.$this->rootPath.$file->getPath(), true, 301);
			exit;
			
		}
		
		/* album requested */
		else if ($album->isAvailable()) {
			$html = '';
			
			/* parent albums */
			$parents = $album->getParents();
			
			/* title */
			$title = $album->getName();
			foreach ($parents as $element) {
				$title .= " - ".$element->getName();
			}
			$this->HTMLTitle = $title;
			
			/* breadcrumbs */
			if (count($parents)) {
				$html .= "<nav class=\"breadcrumbs\">\n".
					"\t<ul>\n";
				foreach (array_reverse($parents) as $element) {
					$path = $element->getPath();
					$html .= "\t\t<li><a href=\"".self::escape($path == '' ? $this->rootScriptClean : $this->rootScript.$path)."\">".self::escape($element->getName())."</a></li>\n";
				}
				$html .= "\t\t<li>".self::escape($album->getName())."</li>\n".
					"\t</ul>\n".
					"</nav>\n\n";
			}
			
			/* album text */
			$albumText = $album->getText();
			$html .= $albumText ? '<div id="album-text">'.$albumText."</div>\n" : '';
			
			/* albums */
			$mode = '140sq';
			if (count($albums = $album->getAlbums())) {
				$html .= "<ul id=\"albums\">\n";
				foreach ($albums as $element) {
					$path = self::escape($this->rootScript.$element->getPath());
					$name = self::escape($element->getName());
					$html .= "\t<li id=\"".$element->getId()."\">\n".
						"\t\t".$element->getThumbHTML($mode)."\n".
						"\t\t<a href=\"".$path."\"><span>".$name."</span></a>\n".
						"\t</li>\n";
				}
				$html .= "</ul>\n\n";
			}
			
			/* files */
			$mode = '300sq';
			$action = $this->defaultFileAction;
			if (count($files = $album->getFiles())) {
				$html .= "<ul id=\"images\">\n";
				foreach ($files as $element) {
					$path = self::escape($this->rootScript.$element->getPath().'/'.$action);
					$name = self::escape($element->getName());
					$html .= "\t<li id=\"".$element->getId()."\">\n".
						"\t\t".$element->getThumbHTML($mode)."\n".
						"\t\t<a href=\"".$path."#image\"><span>".$name."</span></a>\n".
						"\t</li>\n";
				}
				$html .= "</ul>\n\n";
			}
			
			/* empty album */
			else if (!count($albums)) {
				if (empty($request->album)) {
					$html .= "<p>This gallery is empty. Try adding some image files to the directory you placed this script in. You can also have albums by creating directories.</p>";
				} else {
					$html .= "<p>This album is empty.</p>";
				}
			}
			
			/* additional html */
			foreach ($this->callEvent('albumBottomHtml', $album) as $data) {
				$html .= $data;
			}
			
			$this->HTMLContent = $html;
			$this->display();
			exit;
			
		}
		
		/* nothing requested */
		$this->error();
	}
	
	/* display thumb page */
	protected function lonelyPreviewAction(Request $request) {
		
		$album = Factory::createAlbum($request->album);
		$file = Factory::createFile($request->file, $album);
		
		/* file requested */
		if ($file && $file->isAvailable()) {
			
			$html = "";
			$name = self::escape($file->getName());
			$action = $this->defaultFileAction;
			
			/* parent albums */
			$parents = $file->getParents();
			
			/* title */
			$title = $file->getName();
			foreach ($parents as $element) {
				$title .= " - ".$element->getName();
			}
			$this->HTMLTitle = $title;
			
			/* breadcrumbs */
			if (count($parents)) {
				$html .= "<nav class=\"breadcrumbs\">\n".
					"\t<ul>\n";
				foreach (array_reverse($parents) as $element) {
					$path = $element->getPath();
					$html .= "\t\t<li><a href=\"".self::escape($path == '' ? $this->rootScriptClean : $this->rootScript.$path)."\">".self::escape($element->getName())."</a></li>\n";
				}
				$html .= "\t\t<li>".$name."</li>\n".
					"\t</ul>\n".
					"</nav>\n\n";
			}
			
			/* navigation */
			$files = array_values($album->getFiles());
			$count = count($files);
			$current = $file->getFilename();
			foreach ($files as $pos => $f) {
				if ($f->getFilename() === $current) {
					break;
				}
			}
			$first = $pos > 0 ? $files[0] : null;
			$prev = $pos > 0 ? $files[$pos-1] : null;
			$next = ($pos+1) < $count ? $files[$pos+1] : null;
			$last = ($pos+1) < $count ? $files[$count-1] : null;
			if ($pos !== false) {
				$html .= "<nav id=\"imagenav\">\n".
					"\t<p>\n".
					"\t\t".($first ? "<a href=\"".self::escape($this->rootScript.$first->getPath().'/'.$action)."\">first</a>" : "<span>first</span>")."\n".
					"\t\t".($prev ? "<a rel=\"prev\" href=\"".self::escape($this->rootScript.$prev->getPath().'/'.$action)."\">previous</a>" : "<span>previous</span>")."\n".
					"\t\t<a id=\"imagenav-album\" href=\"".self::escape($this->rootScript.$element->getPath())."#".$file->getId()."\">album</a>\n".
					"\t\t".($next ? "<a rel=\"next\" href=\"".self::escape($this->rootScript.$next->getPath().'/'.$action)."\">next</a>" : "<span>next</span>")."\n".
					"\t\t".($last ? "<a href=\"".self::escape($this->rootScript.$last->getPath().'/'.$action)."\">last</a>" : "<span>last</span>")."\n".
					"\t</p>\n".
					"</nav>\n\n";
			}
			
			/* image */
			$html .= "<div class=\"image\">\n";
			
			$html .= "\t<div id=\"image\" class=\"image-box\">\n".
				"\t\t".$file->getPreviewHTML()."\n";
			if ($prev) {
				$html .= "\t\t<a class=\"prev\" rel=\"prev\" href=\"".self::escape($this->rootScript.$prev->getPath().'/'.$action)."#image\"></a>\n";
			}
			if ($next) {
				$html .= "\t\t<a class=\"next\" rel=\"next\" href=\"".self::escape($this->rootScript.$next->getPath().'/'.$action)."#image\"></a>\n";
			}
			$html .= "\t</div>\n\n";
			
			/* info */
			if ($file instanceof ContentFile) {
				$html .= "\t<div class=\"image-info\">\n".
					"\t\t<p class=\"title\">".$name."</p>\n".
					"\t\t<p class=\"download\"><a href=\"".self::escape($this->rootPath.$file->getPath())."\">Download</a></p>\n";
				$dlOpen = false;
				foreach ($this->callEvent('fileInfo', $file) as $data) {
					foreach ($data as $key => $value) {
						if (is_int($key)) {
							if ($dlOpen) {
								$html .= "\t\t</dl>\n";
								$dlOpen = false;
							}
							$html .= "\t\t<p>".self::escape($value)."</p>\n";
						} else {
							if (!$dlOpen) {
								$html .= "\t\t<dl>\n";
								$dlOpen = true;
							}
							$html .= "\t\t\t<dt>".self::escape($key)."</dt>\n".
								"\t\t\t<dd>".self::escape($value)."</dd>\n";
						}
					}
				}
				if ($dlOpen) {
					$html .= "\t\t</dl>\n";
				}
				$html .= "\t</div>\n";
			}
			
			$html .= "</div>\n\n";
			
			$this->HTMLContent = $html;
			$this->display();
			exit;
			
		}
		
		$this->error();
	}
	
	/* shows the thumbnail */
	protected function displayThumb(Request $request, $mode) {
		
		$element = $album = Factory::createAlbum($request->album);
		/* file thumbnail */
		if ($request->file) {
			$element = Factory::createFile($request->file, $album);
		}
		/* album thumbnail */
		else {
			$file = $album->getThumbImage();
			/* own album thumbnail */
			if ($file && $request->action[0] == $file->getFilename()) {
				$element = $file;
			}
		}
		
		if (!$element->isAvailable()) {
			$this->error();
		}
		
		if ($element->initThumb($mode)) {
			/* redirect to thumbnail */
			header("Location: ".$element->getThumbPath($mode));
			exit;
		}
		
		$this->error(500, 'Could not calculate Thumbnail.');
	}
	
	/* shows 140px square thumbnail */
	protected function thumb140sqAction(Request $request) {
		$this->displayThumb($request, '140sq');
	}
	
	/* shows 300px square thumbnail */
	protected function thumb300sqAction(Request $request) {
		$this->displayThumb($request, '300sq');
	}
	
	/* shows 700px thumbnail */
	protected function thumb700pxAction(Request $request) {
		$this->displayThumb($request, '700px');
	}
	
	/* show an error page */
	public function error($errno = 404, $message = "The page you were looking for was not found.") {
		
		/* because this method can be called early, try to init modules */
		if (!$this->_errorInitModules && !$this->_modulesInitialized) {
			/* prevent looping */
			$this->_errorInitModules = true;
			try {
				$this->initModules();
			} catch (Exception $e) {
				/* reset all modules */
				$this->_modules = array();
			}
		}
		
		if (function_exists('http_response_code')) {
			http_response_code($errno);
		} else {
			header(' ', true, $errno);
		}
		$this->HTMLTitle = "Error ".$errno." - ".$this->title;
		$this->HTMLContent = "<p class=\"error\">".self::escape($message)."</p>\n\n";
		$this->display();
		exit;
	}
	
	/* make it UTF-8 */
	public static function utf8ify($string) {
		$encoding = mb_detect_encoding($string, array('UTF-8', 'ISO-8859-1', 'WINDOWS-1252'));
		if ($encoding != 'UTF-8') {
			$string = iconv($encoding, 'UTF-8//TRANSLIT', $string);
		}
		return $string;
	}
	
	/* reduces the string so it only contains alphanumeric chars, dots, dashes and underscores */
	public static function simplifyString($string) {
		return preg_replace('#[^-_\.[:alnum:]]#', '_', $string);
	}
	
	/* HTML escape */
	public static function escape($string) {
		$text = htmlentities(@iconv('UTF-8', 'UTF-8//IGNORE', $string), ENT_QUOTES, 'UTF-8');
		/* umlauts workaround, https://bugs.php.net/bug.php?id=61484 */
		if ($text == '' && $string != '') {
			return preg_replace('#[^-_\w\d ]#', '_', $string);
		}
		return $text;
	}
	
	/* displays the page */
	public function display() {
		?><!DOCTYPE html>
<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<?php
		
		/* optional meta information */
		if (($m = $this->description) != "") {
			echo "\t<meta name=\"description\" content=\"", self::escape($m), "\">\n";
		}
		if (($m = $this->keywords) != "") {
			echo "\t<meta name=\"keywords\" content=\"", self::escape($m), "\">\n";
		}
		if (($m = $this->author) != "") {
			echo "\t<meta name=\"author\" content=\"", self::escape($m), "\">\n";
		}
		if (($m = $this->robots) != "") {
			echo "\t<meta name=\"robots\" content=\"", self::escape($m), "\">\n";
		}
		
		/* CSS */
		$cssfiles = array_merge($this->_design->getCSSFiles(), $this->cssfiles);
		foreach ($cssfiles as $file) {
			echo "\t<link type=\"text/css\" rel=\"stylesheet\" media=\"screen\" href=\"", self::escape($file), "\">\n";
		}
		
		/* JavaScript */
		foreach ($this->jsfiles as $file) {
			echo "\t<script type=\"text/javascript\" src=\"", self::escape($file), "\"></script>\n\n";
		}
		
		/* page title */
		echo "\t<title>", self::escape($this->HTMLTitle ?: $this->title), "</title>\n";
		
		if (isset($this->HTMLHead)) {
			echo strtr($this->HTMLHead, array("\n" => "\n\t"));
		}
	
	?></head>
<body>
	
	<h1><a href="<?php echo self::escape($this->rootScriptClean); ?>"><?php echo self::escape($this->title); ?></a></h1>

	<div id="content">

		<?php if (isset($this->HTMLContent)) {
			echo strtr($this->HTMLContent, array("\n" => "\n\t\t"));
		} ?>
	
	</div>
	
	<?php echo $this->footer; ?>
	
<!-- execution: <?php echo round((microtime(true) - $this->startTime) * 1000, 3); ?> ms -->

</body>
</html><?php
		
	}
}

/* help functions */

/* returns whether any of the patterns is matched */
function preg_match_any(Array $patterns, $value, &$match = null) {
	foreach ($patterns as $pattern) {
		if (preg_match($pattern, $value, $match)) {
			return true;
		}
	}
	return false;
}


abstract class Element extends Component {
	
	/* unique path within the gallery */
	private $_galleryPath;
	
	/* reference to the parent album */
	private $_parent;
	
	/* absolute location on the filesystem */
	protected $location;
	
	/* absolute thumb location on the filesystem, containing "<mode>" which is replaced by the actual mode */
	protected $thumbLocationPattern;
	
	/* relative web path */
	protected $path;
	
	/* cached name of this element */
	private $_name;
	
	/* cached alternative names */
	private $_altNames;
	
	/* unique string that can identify this element */
	private $_id;
	private static $_usedIds = array();
	
	
	function __construct(Array $galleryPath, Album $parent = null) {
		$this->_galleryPath = $galleryPath;
		$this->_parent = $parent;
	}
	
	/* create id */
	protected static function createId($name) {
		$buildString = function($name, $postfix = null) {
			return Lonely::simplifyString($name).($postfix ? '_'.$postfix : '');
		};
		$id = $buildString($name);
		for ($i = 2; in_array($id, self::$_usedIds); ++$i) {
			$id = $buildString($name, $i);
		}
		return $id;
	}
	
	/* inits the id */
	protected function initId($name) {
		self::$_usedIds[] = $this->_id = self::createId($name);
	}
	
	/* returns the gallery path */
	public function getGalleryPath() {
		return $this->_galleryPath;
	}
	
	/* returns the id */
	public function getId() {
		return $this->_id;
	}
	
	/* check if the file or directory is available */
	function isAvailable() {
		return @is_readable($this->location);
	}
	
	/* sets the parent album */
	protected function setParent(Album $parent) {
		$this->_parent = $parent;
	}
	
	/* returns the object of the parent album */
	public function getParent() {
		return $this->_parent;
	}
	
	/* returns an array of the parent albums */
	public function getParents() {
		$albums = array();
		for ($album = $this; $album = $album->getParent();) {
			$albums[] = $album;
		}
		return $albums;
	}
	
	/* returns the absolute file location */
	public function getLocation() {
		return $this->location;
	}
	
	/* returns the absolute thumb file location pattern containing "<mode>" */
	public function getThumbPathPattern() {
		return $this->thumbLocationPattern;
	}
	
	/* returns the name of this element */
	public function getName() {
		if ($this->_name === null) {
			$this->_name = $this->loadName();
		}
		return $this->_name;
	}
	
	/* loads the name of this element */
	protected function loadName() {
		return '';
	}
	
	/* returns the alternative names for this file */
	protected function getAlternativeNames() {
		if ($this->_altNames === null) {
			$this->_altNames = array();
			foreach (Lonely::model()->callEvent('elementNames', $this) as $data) {
				if ($data !== null) {
					$this->_altNames[] = $data;
				}
			}
		}
		return $this->_altNames;
	}
	
	/* returns the first alternative name for this file or null */
	protected function getAlternativeName() {
		$altNames = $this->getAlternativeNames();
		return count($altNames) ? $altNames[0] : null;
	}
	
	/* returns the relative web path */
	public function getPath() {
		return $this->path;
	}
	
	/* returns the absolute path of the thumbnail */
	public function getThumbLocation($mode) {
		$pos = strpos($this->thumbLocationPattern, '<mode>');
		return substr($this->thumbLocationPattern, 0, $pos).$mode.substr($this->thumbLocationPattern, $pos + 6);
	}
	
	/* returns the web thumb path */
	public function getThumbPath($mode) {
		return ($this->thumbAvailable($mode) ? Lonely::model()->thumbPath : Lonely::model()->thumbScript).
			$mode.'/'.$this->path;
	}
	
	/* checks if there is a up-to-date thumbnail file */
	public function thumbAvailable($mode) {
		$thumbPath = $this->getThumbLocation($mode);
		return ($thumbPath && ($tTime = @filemtime($thumbPath)) && ($oTime = @filemtime($this->location)) && $tTime >= $oTime);
	}
	
	/* initializes the thumbnail */
	public function initThumb($mode) {
		/* check if thumbnail is available and up to date */
		return ($this->thumbAvailable($mode) || $this->createThumb($mode, $this->getThumbLocation($mode)));
	}
	
	/* creates a thumbnail */
	protected function createThumb($mode, $saveTo) {
		return false;
	}
	
	/* returns the mime type */
	public function getMime() {
		return 'application/octet-stream';
	}
	
	/* returns the HTML code for the thumbnail */
	public function getThumbHTML($mode) {
		$thumbpath = Lonely::escape($this->getThumbPath($mode));
		$name = Lonely::escape($this->getName());
		return "<img src=\"".$thumbpath."\" alt=\"".$name."\">";
	}
}

class Album extends Element {
	
	/* albums and files in this album */
	private $_albums;
	private $_files;
	
	/* all files in the albums directory */
	private $_allFiles;
	
	/* file to use as thumbnail */
	private $_thumbImage;
	
	/* album description */
	private $_text;
	
	
	function __construct(Array $gPath, self $parent = null) {
		parent::__construct($gPath, $parent);
		
		$gPath = $this->getGalleryPath();
		$this->initId('album_'.end($gPath));
		$this->location = Lonely::model()->rootDir.(count($gPath) ? implode(DIRECTORY_SEPARATOR, $gPath).DIRECTORY_SEPARATOR : '');
		$this->thumbLocationPattern = Lonely::model()->thumbDir.'<mode>'.DIRECTORY_SEPARATOR.(count($gPath) ? implode(DIRECTORY_SEPARATOR, $gPath).DIRECTORY_SEPARATOR : '');
		$this->path = count($gPath) ? implode('/', array_map('rawurlencode', $gPath)).'/' : '';
	}
	
	/* loads the name of this element */
	protected function loadName() {
		if (($altname = $this->getAlternativeName()) !== null) {
			return $altname;
		}
		$gPath = $this->getGalleryPath();
		$name = count($gPath) ? end($gPath) : Lonely::model()->title;
		$name = strtr($name, '_', ' ');
		return $name;
	}
	
	/* returns the object of the parent album */
	public function getParent() {
		$parent = parent::getParent();
		$gPath = $this->getGalleryPath();
		if (!$parent && count($gPath)) {
			$this->setParent($parent = new self(array_slice($gPath, 0, -1)));
		}
		return $parent;
	}
	
	/* reads in the files and dirs within the directory of this album */
	public function loadElements() {
		
		$this->_albums = array();
		$this->_files = array();
		
		/* this is clean if this is a subdirectory which is not the config or thumb directory */
		$gPath = $this->getGalleryPath();
		$cleanLocation = count($gPath) && !in_array(Lonely::model()->configDirectory, $gPath) && $gPath[0] !== Lonely::model()->thumbDirectory;
		
		/* go through each element */
		$dir = opendir($this->location);
		while (($filename = readdir($dir)) !== false) {
			
			/* save the names of all files so that hidden files can be found without accessing the file system again */
			$this->_allFiles[] = $filename;
			
			/* skip files starting with a dot or a minus */
			if (Lonely::model()->isHiddenName($filename)) {
				continue;
			}
			
			/* get location */
			$location = $this->location.$filename;
			
			/* the element must not be in the config or thumb directory */
			if (!$cleanLocation && (strpos($location.DIRECTORY_SEPARATOR, Lonely::model()->configDir) === 0 || strpos($location.DIRECTORY_SEPARATOR, Lonely::model()->thumbDir) === 0)) {
				continue;
			}
			
			switch (filetype($location)) {
				
				case 'dir':
					/* must not be config directory */
					if (!Lonely::model()->isHiddenAlbumName($filename)) {
						$album = Factory::createAlbum(array_merge($gPath, array($filename)));
						$this->_albums[$filename] = $album;
					}
					break;
				
				case 'file':
					if (!Lonely::model()->isHiddenFileName($filename)) {
						$file = Factory::createFile($filename, $this);
						if ($file) {
							$this->_files[$filename] = $file;
						}
					}
					break;
				
			}
			
		}
		
		/* sort alphabetically */
		ksort($this->_albums);
		ksort($this->_files);
		
	}
	
	/* returns the array of albums in this album */
	public function getAlbums() {
		if ($this->_albums === null) {
			$this->loadElements();
		}
		return $this->_albums;
	}
	
	/* returns the array of files in this album */
	public function getFiles() {
		if ($this->_files === null) {
			$this->loadElements();
		}
		return $this->_files;
	}
	
	/* returns the description text */
	public function getText() {
		if ($this->_text === null) {
			$text = @file_get_contents($this->location.Lonely::model()->albumText);
			$this->_text = $text ?: '';
		}
		return $this->_text;
	}
	
	/* returns the thumb image object or false if an own should be rendered */
	public function getThumbImage() {
		if ($this->_thumbImage === null) {
			
			/* by name file */
			if (($path = @file_get_contents($this->location.Lonely::model()->albumThumbFile)) !== false) {
				$file = Factory::createFileByRelPath(trim($path), $this);
				if ($file && $file->isAvailable()) {
					$this->_thumbImage = $file;
				}
			}
			
			/* default name */
			if ($this->_thumbImage === null) {
				$file = Factory::createFile(Lonely::model()->albumThumb, $this);
				if ($file->isAvailable()) {
					$this->_thumbImage = $file;
				} else {
					/* render own */
					$this->_thumbImage = false;
				}
			}
			
		}
		return $this->_thumbImage;
	}
	
	/* returns the absolute path of the thumbnail */
	public function getThumbLocation($mode) {
		if ($thumbImage = $this->getThumbImage()) {
			return $thumbImage->getThumbLocation($mode);
		}
		return parent::getThumbLocation($mode).rawurlencode(Lonely::model()->albumThumb);
	}
	
	/* returns the web thumb path */
	public function getThumbPath($mode) {
		if ($thumbImage = $this->getThumbImage()) {
			return $thumbImage->getThumbPath($mode);
		}
		return parent::getThumbPath($mode).rawurlencode(Lonely::model()->albumThumb);
	}
	
	/* checks if there is a up-to-date thumbnail file */
	public function thumbAvailable($mode) {
		if ($thumbImage = $this->getThumbImage()) {
			return $thumbImage->initThumb($mode);
		}
		$thumbPath = $this->getThumbLocation($mode);
		return ($thumbPath && ($tTime = @filemtime($thumbPath)) &&
			($oTime = @filemtime($this->location)) && $tTime >= $oTime
			&& (!($oTime = @filemtime($this->location.Lonely::model()->albumThumbFile)) || $tTime >= $oTime)
		);
	}
	
	/* creates a thumbnail */
	protected function createThumb($mode, $saveTo) {
		
		/* only 140sq */
		if ($mode != '140sq') return false;
		
		/* number of images */
		$num = max(1, Lonely::model()->albumThumbSquare);
		$n = $num * $num;
		
		/* get images */
		$files = array();
		$fileMode = '300sq';
		/* get files defined by the thumb file */
		if ($pathes = @file($this->location.Lonely::model()->albumThumbFile, FILE_SKIP_EMPTY_LINES)) {
			$numPathes = 0;
			foreach ($pathes as $path) {
				$file = Factory::createFileByRelPath(trim($path), $this);
				if ($file && $file->initThumb($fileMode)) {
					$files[] = $file->getThumbLocation($fileMode);
					++$numPathes;
				}
			}
			$num = ceil(sqrt($numPathes));
			$n = $num * $num - $numPathes;
		}
		/* not enough? get files that are in the album */
		if ($n) {
			foreach($this->getFiles() as $file) {
				if ($file->initThumb($fileMode) && ($thumb = $file->getThumbLocation($fileMode)) && !in_array($thumb, $files)) {
					$files[] = $thumb;
					if (!--$n) {
						break;
					}
				}
			}
		}
		/* not enough? add albums */
		if ($n) {
			foreach($this->getAlbums() as $album) {
				if ($album->initThumb($mode)) {
					array_unshift($files, $album->getThumbLocation($mode));
					if (!--$n) {
						break;
					}
				}
			}
		}
		/* not enough? add duplicates */
		if ($n && $n < ($num * $num)) {
			for ($a = 0; $n; $n--) {
				$files[] = $files[$a++];
			}
		}
		
		/* create new image */
		$thumb = imagecreatetruecolor(140, 140);
		$thumbSize = 140/$num;
		
		/* go through files and add them to the thumbnail */
		$nr = 0;
		foreach ($files as $file) {
			
			/* get info */
			$info = getimagesize($file);
			
			/* calculate dimensions */
			$imageSize = $imageX = $imageY = 0;
			
			/* wider than high */
			if ($info[0] > $info[1]) {
				$imageX = (int)(($info[0] - $info[1]) / 2);
				$imageSize = $info[1];
			}
			/* higher than wide */
			else {
				$imageY = (int)(($info[1] - $info[0]) / 2);
				$imageSize = $info[0];
			}
			
			/* load image from file */
			switch ($info[2]) {
				case IMAGETYPE_GIF: $image = imagecreatefromgif($file); break;
				case IMAGETYPE_JPEG: $image = imagecreatefromjpeg($file); break;
				case IMAGETYPE_PNG: $image = imagecreatefrompng($file); break;
				default: return false;
			}
			
			/* resize */
			$toX = ($nr % $num) * $thumbSize;
			$toY = (int)($nr / $num) * $thumbSize;
			imagecopyresampled($thumb, $image, $toX, $toY, $imageX, $imageY, $thumbSize, $thumbSize, $imageSize, $imageSize);
			
			imagedestroy($image);
			
			++$nr;
		}
		
		/* save */
		
		/* create dir */
		$dir = dirname($saveTo);
		if (!is_dir($dir)) {
			mkdir($dir, 0777, true);
		}
		
		/* write to file */
		return imagejpeg($thumb, $saveTo, 80);
	}
}

class Factory {
	
	/* elements */
	private static $_albums = array();
	private static $_files = array();
	
	
	/* returns the instance of the album */
	public static function createAlbum($gPath) {
		$gPathStr = implode('/', $gPath);
		
		/* check if object was already created */
		if (isset(self::$_albums[$gPathStr])) {
			return self::$_albums[$gPathStr];
		}
		
		/* create object */
		$parentStr = implode('/', array_slice($gPath, 0, -1));
		$parent = isset(self::$_albums[$parentStr]) ? self::$_albums[$parentStr] : null;
		$classname = '\\LonelyGallery\\'.Lonely::model()->albumClass;
		return self::$_albums[$gPathStr] = new $classname($gPath, $parent);
	}
	
	/* returns the instance of the file or null if not supported */
	public static function createFile($filename, Album $parent) {
		$gPath = array_merge($parent->getGalleryPath(), array($filename));
		$gPathStr = implode('/', $gPath);
		
		/* check if object was already created */
		if (isset(self::$_files[$gPathStr])) {
			return self::$_files[$gPathStr];
		}
		
		/* create object */
		$patterns = Lonely::model()->getFilePatterns();
		foreach ($patterns as $pattern => $classname) {
			if (preg_match($pattern, $filename)) {
				return self::$_files[$gPathStr] = new $classname($gPath, $filename, $parent);
			}
		}
		return null;
	}
	
	/* returns the instance of the object by gallery path or null if not supported */
	public static function createFileByRelPath($gPath, Album $album) {
		$path = explode('/', $gPath);
		
		/* relative path */
		if (count($path) == 1 || $path[0] != '') {
			$path = array_merge($album->getGalleryPath(), $path);
		}
		
		/* consolidate path (remove '', '.' and '..')*/
		$path = array_diff($path, array('', '.'));
		foreach ($path as $a => $v) {
			/* delete with previous part */
			if ($v == '..') {
				unset($path[$a]);
				for ($b = $a - 1; $b > 0 && !isset($path[$b]); ) --$b;
				unset($path[$b]);
			}
		}
		
		/* load objects */
		$album = self::createAlbum(array_slice($path, 0, -1));
		return self::createFile(end($path), $album);
	}
}

abstract class File extends Element {
	
	/* filename on the file system */
	private $_filename;
	
	
	function __construct($gPath, $filename, Album $parent) {
		$this->_filename = $filename;
		parent::__construct($gPath, $parent);
		
		$this->initId('file_'.$this->_filename);
		if ($this->_filename !== "") {
			$parent = $this->getParent();
			$this->location = $parent->getLocation().$this->_filename;
			$this->thumbLocationPattern = $parent->getThumbPathPattern().$this->_filename;
			$this->path = $parent->getPath().rawurlencode($this->_filename);
		}
	}
	
	/* file pattern */
	public static function pattern() {
		return '/^$/'; // empty pattern to match nothing
	}
	
	/* loads the name of this element */
	protected function loadName() {
		if (($altname = $this->getAlternativeName()) !== null) {
			return $altname;
		}
		return $this->getFilename();
	}
	
	/* returns the filename */
	public function getFilename() {
		return $this->_filename;
	}
	
	/* returns the HTML code for the preview */
	public function getPreviewHTML() {
		return "";
	}
}

abstract class MetaFile extends File {
	
}

abstract class ContentFile extends File {
	
}

class Image extends ContentFile {
	
	private $_imageInfo;
	private $_useOriginalAsThumb = array();
	
	
	/* file pattern */
	public static function pattern() {
		return '/\.(png|jpe?g|gif)$/i';
	}
	
	/* loads the name of this element */
	protected function loadName() {
		if (($altname = $this->getAlternativeName()) !== null) {
			return $altname;
		}
		$name = $this->getFilename();
		$name = substr($name, 0, strrpos($name, '.'));
		$name = strtr($name, '_', ' ');
		return $name;
	}
	
	/* returns the image info */
	public function getImageInfo() {
		if (!$this->_imageInfo) {
			$this->_imageInfo = @getimagesize($this->location);
		}
		return $this->_imageInfo;
	}
	
	/* returns the mime type */
	public function getMime() {
		$info = $this->getImageInfo();
		return $info['mime'];
	}
	
	/* returns the HTML code for the preview */
	public function getPreviewHTML() {
		$path = empty(Lonely::model()->useOriginals) ? $this->getThumbPath('700px') : Lonely::model()->rootPath.$this->path;
		$name = Lonely::escape($this->getName());
		return "<img src=\"".Lonely::escape($path)."\" alt=\"".$name."\">\n".
			"<script type=\"text/javascript\">\n".
			"<!--\n".
			"adjustMaxImageHeight();\n".
			"-->\n".
			"</script>";
	}
	
	/* returns whether this file is suitable as a thumb without resizing */
	public function canUseOriginalAsThumb($mode) {
		if (!isset($this->_useOriginalAsThumb[$mode])) {
			/* evaluate whether this file has to be resized */
			
			/* get info */
			$info = $this->getImageInfo();
			
			$v = false;
			if ($info) {
				switch ($mode) {
					case '140sq': $v = ($info[0] == $info[1] && $info[0] <= 140); break;
					case '300sq': $v = ($info[0] == $info[1] && $info[0] <= 300); break;
					case '700px': $v = ($info[0] <= 700 && $info[1] <= 700); break;
				}
			}
			$this->_useOriginalAsThumb[$mode] = $v;
		}
		return $this->_useOriginalAsThumb[$mode];
	}
	
	/* returns the absolute path of the thumbnail */
	public function getThumbLocation($mode) {
		return $this->canUseOriginalAsThumb($mode) ? $this->getLocation() : parent::getThumbLocation($mode);
	}
	
	/* returns the web thumb path */
	public function getThumbPath($mode) {
		return $this->canUseOriginalAsThumb($mode) ? Lonely::model()->rootPath.$this->path : parent::getThumbPath($mode);
	}
	
	/* checks if there is a up-to-date thumbnail file */
	public function thumbAvailable($mode) {
		return ($this->canUseOriginalAsThumb($mode) || parent::thumbAvailable($mode));
	}
	
	/* creates a thumbnail */
	protected function createThumb($mode, $saveTo) {
		
		/* check if creating a thumbnail is needless */
		if ($this->canUseOriginalAsThumb($mode)) {
			return true;
		}
		
		/* get info */
		$info = $this->getImageInfo();
		
		/* modes */
		switch ($mode) {
			case '140sq': $square = true; $maxWidth = $maxHeight = 140; break;
			case '300sq': $square = true; $maxWidth = $maxHeight = 300; break;
			case '700px': $square = false; $maxWidth = $maxHeight = 700; break;
			default: return false;
		}
		
		/* calculate dimensions */
		$imageWidth = $info[0];
		$imageHeight = $info[1];
		$imageX = $imageY = 0;
		
		/* square mode */
		if ($square) {
			
			/* thumb dimensions */
			if ($imageWidth < $maxWidth || $imageHeight < $maxHeight) {
				$thumbWidth = $thumbHeight = min($imageWidth, $imageHeight);
			} else {
				$thumbWidth = $maxWidth;
				$thumbHeight = $maxHeight;
			}
			
			/* wider than high */
			if ($imageWidth > $imageHeight) {
				$imageX = floor(($imageWidth - $imageHeight) / 2);
				$imageWidth = $imageHeight;
			}
			/* higher than wide */
			else {
				$imageY = floor(($imageHeight - $imageWidth) / 2);
				$imageHeight = $imageWidth;
			}
			
		}
		
		/* normal mode */
		else {
			
			/* image is smaller than the max dimension: keep original width and height */
			if ($imageWidth < $maxWidth && $imageHeight < $maxHeight) {
				$thumbWidth = $imageWidth;
				$thumbHeight = $imageHeight;
			}
			/* wider than high */
			else if ($imageWidth > $imageHeight) {
				$thumbWidth = $maxWidth;
				$thumbHeight = $maxWidth / $imageWidth * $imageHeight;
			}
			/* higher than wide */
			else {
				$thumbWidth = $maxHeight / $imageHeight * $imageWidth;
				$thumbHeight = $maxHeight;
			}
			
		}
		
		/* load image from file */
		switch ($info[2]) {
			case IMAGETYPE_GIF: $image = imagecreatefromgif($this->location); break;
			case IMAGETYPE_JPEG: $image = imagecreatefromjpeg($this->location); break;
			case IMAGETYPE_PNG: $image = imagecreatefrompng($this->location); break;
			default: return false;
		}
		
		/* create dir */
		$dir = dirname($saveTo);
		if (!is_dir($dir)) {
			mkdir($dir, 0777, true);
		}
		
		/* resizing */
		
		/* create new image */
		$thumb = imagecreatetruecolor($thumbWidth, $thumbHeight);
		
		/* transparency for gif and png */
		if (in_array($info[2], array(IMAGETYPE_GIF, IMAGETYPE_PNG))) {
			$transparent = imagecolorallocatealpha($thumb, 0, 0, 0, 127);
			imagecolortransparent($thumb, $transparent);
			imagefill($thumb, 0, 0, $transparent);
			imagealphablending($thumb, false);
			imagesavealpha($thumb, true);
		}
		
		/* copy to thumb */
		imagecopyresampled($thumb, $image, 0, 0, $imageX, $imageY, $thumbWidth, $thumbHeight, $imageWidth, $imageHeight);
		
		/* save */
		
		/* write to file */
		switch ($info[2]) {
			case IMAGETYPE_GIF: return imagegif($thumb, $saveTo);
			case IMAGETYPE_JPEG: return imagejpeg($thumb, $saveTo, 80);
			case IMAGETYPE_PNG: return imagepng($thumb, $saveTo, 9);
		}
	}
}

class GenericFile extends ContentFile {
	
	protected $thumbLocationPattern;
	protected $genericFileName = 'default.png';
	
	function __construct($gPath, $filename, Album $parent) {
		parent::__construct($gPath, $filename, $parent);
		
		if ($this->getFilename() !== "") {
			$this->thumbLocationPattern = Lonely::model()->thumbDir.'generic'.DIRECTORY_SEPARATOR.'<mode>'.DIRECTORY_SEPARATOR.$this->genericFileName;
		}
	}
	
	/* file pattern */
	public static function pattern() {
		return '/('.implode('|', Lonely::model()->extensions).')$/i';
	}
	
	/* returns the HTML code for the preview */
	public function getPreviewHTML() {
		$path = Lonely::escape($this->getThumbPath('700px'));
		$name = Lonely::escape($this->getName());
		return "<img src=\"".$path."\" alt=\"".$name."\">";
	}
	
	/* returns the web thumb path */
	public function getThumbPath($mode) {
		return $this->thumbAvailable($mode) ? Lonely::model()->thumbPath.'generic/'.$mode.'/'.$this->genericFileName : Lonely::model()->thumbScript.$mode.'/'.$this->path;
	}
	
	/* checks if there is a up-to-date thumbnail file */
	public function thumbAvailable($mode) {
		$thumbPath = $this->getThumbLocation($mode);
		return ($thumbPath && is_file($thumbPath));
	}
	
	/* creates a thumbnail */
	protected function createThumb($mode, $saveTo) {
		/* create dir */
		$dir = dirname($saveTo);
		if (!is_dir($dir)) {
			mkdir($dir, 0777, true);
		}
		
		/* save file */
		switch ($mode) {
			
			case '140sq':
				$thumb = imagecreatetruecolor(140, 140);
				$image = imagecreatefromstring(base64_decode($this->base64EncodedThumbFile));
				imagecopyresampled($thumb, $image, 0, 0, 0, 0, 140, 140, 300, 300);
				imagedestroy($image);
				return imagepng($thumb, $saveTo, 9);
			
			case '300sq':
			default:
				return file_put_contents($saveTo, base64_decode($this->base64EncodedThumbFile));
			
		}
	}
	
	protected $base64EncodedThumbFile = 'iVBORw0KGgoAAAANSUhEUgAAASwAAAEsCAYAAAB5fY51AAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAAsTAAALEwEAmpwYAAADzUlEQVR42u3YwQ2CQBRFUTS0oQspgxUNaQW4oANdUY0xUctgo/ShNZgYne8/pwIYkps3LPrr5lkBBLB0BIBgAQgWIFgAggUgWIBgAQgWgGABggUgWACCBQgWgGABCBYgWACCBSBYgGABCBaAYAGCBSBYAIIFCBaAYAEIFiBYAIIFCBaAYAEIFiBYAIIFIFiAYAEIFoBgAYIFUJK69Acc2slXgi/Z3xoLC0CwAMECKFEd7YFLv2NDJNH+EVtYgGABCBYgWACCBSBYgGABCBaAYAGCBSBYAIIFCBaAYAEIFiBYAIIFIFiAYAEIFoBgAYIFIFgAggUIFoBgAQgWIFgAggUIliMABAtAsADBAhAsAMECBAtAsAAECxAsAMECECxAsAAEC0CwAMECECwAwQIEC0CwAAQLECwAwQIQLECwAAQLQLAAwQIQLECwAAQLQLAAwQIQLADBAgQLQLAABAsQLADBAhAsQLAABAtAsADBAhAsAMECBAtAsAAECxAsAMECECxAsAAEC0CwAMECECxAsAAEC0CwAMECECwAwQIEC0CwAAQLECwAwQIQLECwAAQLQLAAwQIQLADBAgQLQLAABAsQLADBAhAsQLAABAtAsADBAhAsQLAABAtAsADBAhAsAMECBAtAsAAECxAsAMECECxAsAAEC0CwAMECECwAwQIEC0CwAAQLECwAwQIQLCCm2hHw74Z2cggWFoBgAQgWEJt/WKSzvzXpz6BbbatuvbOwgJixOt0PggXEiNX5cRQsoOxYXeYxRKyqyj8ssKyCxMrCAssq1LtYWGBZWViAWAkWkDJWggViJViAWAkWkDZWggViJViAWAkWkDZWggViJViAWAkWkDZWggViJViAWAkWiFXaWAkWiJVgAWIlWCBWaWMlWCBWggWIlWCBWAkWIFaCBYiVYIFYCRYgVoIFiJVggVgJFiBWggViJVaCBWIlWIBYCRaIlVgJFoiVYAFiJVggVggWiJVgAWIlWCBWCBaIlWABYiVYIFYIFoiVYIFYiZVggVghWCBWggVihWCBWCFYIFaCBWKFYIFYCRYgVoIFYoVggVgJFoiVWAkWiBWCBWIlWCBWYiVYIFYIFoiVYIFYIVjwq2CJVVh1tAce2slX46Mu8yhWggXls6xcCcGywsICyyq3RX/dPB0D4EoIIFiAYAEIFoBgAYIFIFgAggUIFoBgAQgWIFgAggUgWIBgAQgWgGABggUgWACCBQgWgGABCBYgWACCBSBYgGABCBYgWACCBSBYgGABCBaAYAGCBSBYAIIFCBaAYAG86QXYMa4//4/U4QAAAABJRU5ErkJggg==';
}


/* class to extend when developing a module */
abstract class Module {
	
	/* sets the LonelyGallery reference */
	function __construct() {
		$this->afterConstruct();
	}
	
	/* executed after __construct() */
	public function afterConstruct() {
		/* nothing */
	}
	
	/* returns settings for lonely */
	public function settings() {
		return array();
	}
	
	/* returns array of file classes to priority */
	public function fileClasses() {
		return array();
	}
	
	/* handle request */
	public function handleRequest(Request $request) {
		/* return true if further requests handling is allowed */
		/* return false if the request is handled */
		return true;
	}
}

/* class to extend when developing a design */
abstract class Design extends Module {
	
	/* returns an array with css files to be loaded as design */
	public function getCSSFiles() {
		return array();
	}
}

/* default design */
namespace LonelyGallery\DefaultDesign;
use \LonelyGallery\Lonely as Lonely;
class Module extends \LonelyGallery\Design {
	
	/* returns settings for default design */
	public function afterConstruct() {
		Lonely::model()->jsfiles[] = Lonely::model()->configScript.'lonely.js';
	}
	
	/* returns an array with css files to be loaded as design */
	public function getCSSFiles() {
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
    margin: 20px 0;
	background-color: #111;
	color: #fff;
	font-family: Arial,Helvetica,sans-serif;
	font-size: 14px;
	width: 100%;
}
body > *:not(#content), #content > *:not(.image), .image > *:not(.image-box) {
	margin-left: 20px;
	margin-right: 20px;
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
.breadcrumbs ul {
	margin-bottom: 0;
}
.breadcrumbs ul li {
	display: inline;
}
.breadcrumbs ul li:before {
	content: " >> ";
}
.breadcrumbs ul li:first-child:before {
	content: "";
}
#content {
	margin-bottom: 40px;
}
#albums, #images {
	overflow: auto;
	padding: 0;
	margin: 10px auto 20px;
}
#albums {
	margin-bottom: 12px;
}
#albums > li, #images > li {
	position: relative;
	display: block;
	float: left;
	width: 140px;
	height: 140px;
	overflow: hidden;
	background-color: #111;
	text-align: center;
	line-height: 120px;
	margin: 0 10px 10px 0;
}
#images > li {
	width: 300px;
	height: 300px;
	line-height: 280px;
}
#images > li > img {
	height: 300px;
	width: 300px;
}
#albums > li > img {
	height: 140px;
	width: 140px;
}
#albums > li > a, #images > li > a {
	color: #fff;
	position: absolute;
	top: 0;
	left: 0;
	width: 120px;
	height: 120px;
	padding: 10px;
	background-color: rgba(0,0,0,0);
	transition: background-color 0.3s;
	-moz-transition: background-color 0.3s;
	-webkit-transition: background-color 0.3s;
	-o-transition: background-color 0.3s;
}
#images > li > a {
	width: 280px;
	height: 280px;
	background-color: rgba(0,0,0,.4);
	opacity: 0;
	transition: opacity 0.3s;
	-moz-transition: opacity 0.3s;
	-webkit-transition: opacity 0.3s;
	-o-transition: opacity 0.3s;
}
#albums > li > a:hover, #albums > li > a:focus {
	background-color: rgba(0,0,0,.4);
}
#images > li > a:hover, #images > li > a:focus {
	opacity: 1;
}
#albums > li > a span, #images > li > a span {
	background-color: #111;
	display: inline-block;
	line-height: 150%;
	padding: 4px 8px;
	box-shadow: 0 0 2px #111;
	vertical-align: middle;
}
#imagenav, .image {
	text-align: center;
}
#imagenav p {
	margin: 0;
}
#imagenav p * {
	display: inline-block;
	line-height: 400%;
}
#imagenav p *:nth-child(1):before { content: "<< "; }
#imagenav p *:nth-child(2):before { content: "< "; }
#imagenav p *:nth-child(3):before { content: "["; }
#imagenav p *:nth-child(3):after { content: "]"; }
#imagenav p *:nth-child(4):after { content: " >"; }
#imagenav p *:nth-child(5):after { content: " >>"; }
.image {
	margin: 0 auto;
}
.image img {
	display: block;
	max-width: 100%;
	margin: 0 auto;
}
.breadcrumbs + .image, #album-text + .image, .image + .image, .image:first-child {
	margin-top: 56px;
}
.image-box {
	display: inline-block;
	position: relative;
	margin: 0 0 10px;
	max-width: 100%;
	min-width: 100px;
}
.image-box a {
	position: absolute;
	top: 0;
	left: 0;
	height: 100%;
	width: 40%;
	min-width: 50px;
	color: #fff;
	opacity: 0;
	transition: opacity 0.3s;
	-moz-transition: opacity 0.3s;
	-webkit-transition: opacity 0.3s;
	-o-transition: opacity 0.3s;
	text-shadow: #000 0px 0px 10px;
}
.image-box a:hover, .image-box a:focus {
	opacity: 1;
}
.image-box a.next {
	right: 0;
	left: auto;
}
.image-box a.prev:before, .image-box a.next:after {
	content: "<";
	display: block;
	font-size: 80px;
	margin-top: -40px;
	position: absolute;
	width: 100%;
	top: 50%;
}
.image-box a.next:after {
	content: ">";
}
.image-info p, .image-info dl {
    margin: 4px;
	text-align: left;
}
.image-info p.title, .image-info p.download {
    text-align: center;
}
.image-info p.title {
    font-size: 16px;
	color: #fff;
}
.image-info p.title:before, .image-info p.title:after {
    content: "» ";
	color: #666;
	font-size: 24px;
	vertical-align: baseline;
}
.image-info p.title:after {
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
	var div = document.getElementById('content').getElementsByTagName('div');
	for (var i = 0; i < div.length; ++i) {
		if (div[i].className == 'image') {
			var img = div[i].getElementsByTagName('img');
			if (img.length) {
				img[0].style.maxHeight = window.innerHeight + 'px';
			}
		}
	}
}
function navigate(event) {
	var k = event.keyCode;
	switch (k) {
		case 37: // left arrow
		case 39: // right arrow
			var i = document.getElementById('image');
			if (i) {
				var a = i.getElementsByTagName('a');
				for (var i = 0; i < a.length; ++i) {
					if ((k == 37 && a[i].className == 'prev') || (k == 39 && a[i].className == 'next')) {
						window.location = a[i].href;
						return false;
					}
				}
			}
			break;
	}
}
window.addEventListener('load', adjustMaxImageHeight);
window.addEventListener('resize', adjustMaxImageHeight);
window.addEventListener('keydown', navigate);
<?php
		exit;
	}
}
?>