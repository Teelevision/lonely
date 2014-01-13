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
	public $albumClass = '\\LonelyGallery\\Album';
	
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
		
		/* initialize modules */
		$this->initModules();
		
		/* init render profiles */
		$renderProfiles = $this->_design->renderProfiles();
		RenderHelper::addProfiles($renderProfiles);
		
		/* init request */
		$scopePattern = '#^('.preg_quote($this->configDirectory).'|'.preg_quote($this->thumbDirectory).'/('.implode('|', array_map('preg_quote', array_keys($renderProfiles), array('#'))).'))(/|$)#';
		$this->request = new Request($scopePattern);
		$album = $this->request->album;
		
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
			foreach ($album as $a) {
				if ($a && $this->isHiddenAlbumName($a)) {
					$this->error();
				}
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
				$method = 'displayThumb';
				break;
				
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
	
	/* returns the module */
	public function getModule($module) {
		return isset($this->_modules[$module]) ? $this->_modules[$module] : null;
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
	
	/* returns the design */
	public function getDesign() {
		return $this->_design;
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
			
			$html = "<section class=\"album\">\n\n";
			
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
				$html .= "\t<header>\n".
					"\t\t<ul class=\"breadcrumbs\">\n";
				foreach (array_reverse($parents) as $element) {
					$path = $element->getPath();
					$html .= "\t\t\t<li><a href=\"".self::escape($path == '' ? $this->rootScriptClean : $this->rootScript.$path)."\">".self::escape($element->getName())."</a></li>\n";
				}
				$html .= "\t\t\t<li>".self::escape($album->getName())."</li>\n".
					"\t\t</ul>\n".
					"\t</header>\n\n";
			}
			
			/* album text */
			$albumText = $album->getText();
			$html .= $albumText ? "\t<div class=\"album-text\">".$albumText."</div>\n\n" : "";
			
			/* links */
			$html2 = "";
			foreach ($this->callEvent('albumLinks', $album) as $datas) {
				foreach ($datas as $data) {
					$html2 .= "\t\t<li><a href=\"".self::escape($data['url'])."\">".self::escape($data['label'])."</a></li>\n";
				}
			}
			if ($html2 != "") {
				$html .= "\t<ul class=\"links\">\n".
					"\t\t<li class=\"active\"><span>index</span></li>\n".
					$html2.
					"\t</ul>\n\n";
			}
			
			/* albums */
			if (count($albums = $album->getAlbums())) {
				$html .= "\t<ul class=\"albums\">\n";
				foreach ($albums as $element) {
					$path = self::escape($this->rootScript.$element->getPath());
					$name = self::escape($element->getName());
					$html .= "\t\t<li id=\"".$element->getId()."\">\n".
						"\t\t\t".$element->getThumbHTML($this->_design->thumbProfile($element))."\n".
						"\t\t\t<a class=\"thumb-link\" href=\"".$path."\">\n".
						"\t\t\t\t<span>".$name."</span>\n".
						"\t\t\t</a>\n".
						"\t\t</li>\n";
				}
				$html .= "\t</ul>\n\n";
			}
			
			/* files */
			$action = $this->defaultFileAction;
			if (count($files = $album->getFiles())) {
				$html .= "\t<ul class=\"files\">\n";
				foreach ($files as $element) {
					$path = self::escape($this->rootScript.$element->getPath().'/'.$action);
					$name = self::escape($element->getName());
					$html .= "\t\t<li id=\"".$element->getId()."\">\n".
						"\t\t\t".$element->getThumbHTML($this->_design->thumbProfile($element))."\n".
						"\t\t\t<a class=\"thumb-link\" href=\"".$path."#p\">\n".
						"\t\t\t\t<span>".$name."</span>\n".
						"\t\t\t</a>\n".
						"\t\t</li>\n";
				}
				$html .= "\t</ul>\n\n";
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
			
			$html = "<section class=\"file\">\n\n".
				"\t<header>\n\n";
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
				$html .= "\t\t<ul class=\"breadcrumbs\">\n";
				foreach (array_reverse($parents) as $element) {
					$path = $element->getPath();
					$html .= "\t\t\t<li><a href=\"".self::escape($path == '' ? $this->rootScriptClean : $this->rootScript.$path)."\">".self::escape($element->getName())."</a></li>\n";
				}
				$html .= "\t\t\t<li>".$name."</li>\n".
					"\t\t</ul>\n\n";
			}
			
			/* links */
			$html2 = "";
			foreach ($this->callEvent('fileLinks', $file) as $datas) {
				foreach ($datas as $data) {
					$html2 .= "\t\t<li><a href=\"".self::escape($data['url'])."\">".self::escape($data['label'])."</a></li>\n";
				}
			}
			if ($html2 != "") {
				$html .= "\t<ul class=\"links\">\n".
					"\t\t<li class=\"active\"><span>preview</span></li>\n".
					$html2.
					"\t</ul>\n\n";
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
				$html .= "\t\t<p class=\"nav\">\n".
					"\t\t\t".($first ? "<a class=\"nav-first\" href=\"".self::escape($this->rootScript.$first->getPath().'/'.$action)."\">first</a>" : "<span class=\"nav-first\">first</span>")."\n".
					"\t\t\t".($prev ? "<a class=\"nav-prev\" rel=\"prev\" href=\"".self::escape($this->rootScript.$prev->getPath().'/'.$action)."\">previous</a>" : "<span class=\"nav-prev\">previous</span>")."\n".
					"\t\t\t<a class=\"nav-album\" href=\"".self::escape($this->rootScript.$element->getPath())."#".$file->getId()."\">album</a>\n".
					"\t\t\t".($next ? "<a class=\"nav-next\" rel=\"next\" href=\"".self::escape($this->rootScript.$next->getPath().'/'.$action)."\">next</a>" : "<span class=\"nav-next\">next</span>")."\n".
					"\t\t\t".($last ? "<a class=\"nav-last\" href=\"".self::escape($this->rootScript.$last->getPath().'/'.$action)."\">last</a>" : "<span class=\"nav-last\">last</span>")."\n".
					"\t\t</p>\n\n";
			}
			
			$html .= "\t</header>\n\n";
			
			/* preview */
			$html .= "\t<div id=\"p\" class=\"preview-box\">\n".
				"\t\t".$file->getPreviewHTML()."\n";
			if ($prev) {
				$html .= "\t\t<a class=\"nav prev\" rel=\"prev\" href=\"".self::escape($this->rootScript.$prev->getPath().'/'.$action)."#p\"></a>\n";
			}
			if ($next) {
				$html .= "\t\t<a class=\"nav next\" rel=\"next\" href=\"".self::escape($this->rootScript.$next->getPath().'/'.$action)."#p\"></a>\n";
			}
			$html .= "\t</div>\n\n";
			
			/* info */
			if ($file instanceof ContentFile || $file->showTitle) {
				$html .= "\t<div class=\"info\">\n".
					"\t\t<p class=\"title\">".$name."</p>\n";
				if ($file instanceof ContentFile) {
					$html .= "\t\t<p class=\"download\"><a href=\"".self::escape($this->rootPath.$file->getPath())."\">Download</a></p>\n";
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
			}
			
			$html .= "</section>\n";
			
			$this->HTMLContent = $html;
			$this->display();
			exit;
			
		}
		
		$this->error();
	}
	
	/* shows the thumbnail */
	protected function displayThumb(Request $request) {
		$profile = implode('/', array_slice($request->scope, 1));
		
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
		
		if ($element->initThumb($profile)) {
			/* redirect to thumbnail */
			header("Location: ".$element->getThumbPath($profile));
			exit;
		}
		
		$this->error(500, 'Could not calculate Thumbnail.');
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
	
	/* reduces the string so it only contains alphanumeric chars, dashes and underscores */
	public static function simplifyString($string) {
		return preg_replace('#[^-_[:alnum:]]#', '_', $string);
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
		$cssfiles = array_merge($this->_design->cssFiles(), $this->cssfiles);
		foreach ($cssfiles as $file) {
			echo "\t<link type=\"text/css\" rel=\"stylesheet\" media=\"screen\" href=\"", self::escape($file), "\">\n";
		}
		
		/* JavaScript */
		foreach ($this->jsfiles as $file) {
			echo "\t<script type=\"text/javascript\" src=\"", self::escape($file), "\"></script>\n";
		}
		
		/* page title */
		echo "\t<title>", self::escape($this->HTMLTitle ?: $this->title), "</title>\n";
		
		if (isset($this->HTMLHead)) {
			echo strtr($this->HTMLHead, array("\n" => "\n\t"));
		}
	
	?></head>
<body>
	
	<h1>
		<a href="<?php echo self::escape($this->rootScriptClean); ?>"><?php echo self::escape($this->title); ?></a>
	</h1>

	<div id="content">

		<?php if (isset($this->HTMLContent)) {
			echo strtr($this->HTMLContent, array("\n" => "\n\t\t"));
		} ?>
	
	</div>
	
	<?php echo $this->footer."\n"; ?>
	
<?php if ($this->adjustMaxImageHeight) { ?>
<script type="text/javascript">
<!--
adjustMaxImageHeight();
-->
</script>
<?php } ?>
	
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


class Renderer {
	
	/* settings:
		height/width: fixed height/width, missing value is scaled accordingly
		max-height/width: like height/width, but don't enlarge
		quality: JPEG quality, default: 80
		square: whether aspect ratio is 1:1, when active use (max-)width only */
	protected $s;
	
	
	/* init with profile name */
	public function __construct(Array $settings) {
		$this->s = $settings + array(
			'width' => 0,
			'height' => 0,
			'max-width' => 0,
			'max-height' => 0,
			'quality' => 80,
			'square' => false,
		);
	}
	
	/* returns whether the given file matches the requirements */
	public function isSuitable($path) {
		
		/* get info */
		$info = @getimagesize($path);
		
		/* check */
		return ($this->s['width'] && $info[0] == $this->s['width'])
			&& ($this->s['height'] && $info[1] == $this->s['height'])
			&& ($this->s['max-width'] && $info[0] <= $this->s['max-width'])
			&& ($this->s['max-height'] && $info[1] <= $this->s['max-height'])
			&& ($this->s['square'] && $info[0] == $info[1]);
	}
	
	/* render thumbnail from image */
	public function renderThumbnail($path, $saveTo) {
		
		/* get info */
		$info = static::getInfo($path);
		
		/* calculate dimensions */
		$thumbWidth = $this->s['width'];
		$thumbHeight = $this->s['square'] ? $this->s['width'] : $this->s['height'];
		if ($thumbWidth && !$thumbHeight) {
			$thumbHeight = $thumbWidth / $info[0] * $info[1];
		} else if (!$thumbWidth && $thumbHeight) {
			$thumbWidth = $thumbHeight / $info[0] * $info[1];
		} else if (!$thumbWidth && !$thumbHeight) {
			/* 1:1 aspect ratio */
			if ($this->s['square']) {
				$thumbWidth = $thumbHeight = min($info[0], $info[1], $this->s['max-width']);
			}
			/* normal mode */
			else {
				$thumbWidth = $info[0];
				$thumbHeight = $info[1];
				if ($this->s['max-width'] && $thumbWidth > $this->s['max-width']) {
					$thumbWidth = $this->s['max-width'];
					$thumbHeight = $this->s['max-width'] / $info[0] * $info[1];
				}
				if ($this->s['max-height'] && $thumbHeight > $this->s['max-height']) {
					$thumbHeight = $this->s['max-height'];
					$thumbWidth = $this->s['max-height'] / $info[1] * $info[0];
				}
			}
		}
		/* calculate crop infos */
		$imageX = $imageY = 0;
		$imageWidth = $info[0];
		$imageHeight = $info[1];
		if ($this->s['square']) {
			$imageWidth = $imageHeight = min($info[0], $info[1]);
			$imageX = floor(($info[0] - $imageWidth) / 2);
			$imageY = floor(($info[1] - $imageWidth) / 2);
		}
		
		/* create new image */
		$thumb = static::createImage($thumbWidth, $thumbHeight, $info[2]);
		
		/* load image from file */
		$image = static::loadImage($path, $info[2]);
		
		/* resizing */
		static::copyImage($thumb, $image, 0, 0, $imageX, $imageY, $thumbWidth, $thumbHeight, $imageWidth, $imageHeight);
		
		/* write to file */
		return static::saveImage($thumb, $saveTo, $info[2]);
	}
	
	/* render checkboard pattern from images */
	public function renderChessboard(Array $files, $saveTo) {
	
		/* prepare */
		$num = (int)sqrt(count($files));
		$thumbSize = $this->s['width']/$num;
		
		/* create new image */
		$thumb = self::createImage($this->s['width'], $this->s['width'], IMAGETYPE_JPEG);
		
		/* go through files and add them to the thumbnail */
		$nr = 0;
		foreach ($files as $file) {
			
			/* get info */
			$info = static::getInfo($file);
			
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
			$image = static::loadImage($file, $info[2]);
			
			/* resize */
			$toX = ($nr % $num) * $thumbSize;
			$toY = (int)($nr / $num) * $thumbSize;
			self::copyImage($thumb, $image, $toX, $toY, $imageX, $imageY, $thumbSize, $thumbSize, $imageSize, $imageSize);
			
			static::unsetImage($image);
			
			++$nr;
		}
		
		/* write to file */
		return self::saveImage($thumb, $saveTo, IMAGETYPE_JPEG);
	}
	
	/* returns the info of an image */
	protected static function getInfo($path) {
		return @getimagesize($path);
	}
	
	/* copys (a part of) an image into another image */
	protected static function copyImage(&$dest, $src, $destX, $destY, $srcX, $srcY, $destWidth, $destHeight, $srcWidth, $srcHeight) {
		return imagecopyresampled($dest, $src, $destX, $destY, $srcX, $srcY, $destWidth, $destHeight, $srcWidth, $srcHeight);
	}
	
	/* creates a new image */
	protected static function createImage($width, $height, $type) {
		$image = imagecreatetruecolor($width, $height);
		/* transparency for gif and png */
		if (in_array($type, array(IMAGETYPE_GIF, IMAGETYPE_PNG))) {
			$transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
			imagecolortransparent($image, $transparent);
			imagefill($image, 0, 0, $transparent);
			imagealphablending($image, false);
			imagesavealpha($image, true);
		}
		return $image;
	}
	
	/* loads an image */
	protected static function loadImage($path, $type) {
		switch ($type) {
			case IMAGETYPE_GIF: return imagecreatefromgif($path);
			case IMAGETYPE_JPEG: return imagecreatefromjpeg($path);
			case IMAGETYPE_PNG: return imagecreatefrompng($path);
		}
		return null;
	}
	
	/* saves an image */
	protected static function saveImage($image, $path, $type) {
		
		/* create dir */
		$dir = dirname($path);
		if (!is_dir($dir)) {
			mkdir($dir, -1, true);
		}
		
		switch ($type) {
			case IMAGETYPE_GIF: return imagegif($image, $path);
			case IMAGETYPE_JPEG: return imagejpeg($image, $path, 80);
			case IMAGETYPE_PNG: return imagepng($image, $path, 9);
		}
		return false;
	}
	
	/* removes an image from the memory */
	protected static function unsetImage($image) {
		return imagedestroy($image);
	}
}

class RenderHelper extends Renderer {
	
	/* available profiles */
	private static $_profiles = array();
	
	/* current profile */
	protected $profile;
	
	/* instances */
	private static $_instances = array();
	
	
	/* init with profile name */
	public function __construct($profile) {
		$this->profile = $profile;
		parent::__construct(self::$_profiles[$profile]);
	}
	
	/* return instance of this class with the given profile */
	public static function profile($profile) {
		if (!isset(self::$_instances[$profile])) {
			self::$_instances[$profile] = new static($profile);
		}
		return self::$_instances[$profile];
	}
	
	/* add profiles in the form array(name => settings, ...) */
	public static function addProfiles($profiles) {
		self::$_profiles += $profiles;
	}
	
	/* render thumbnail from image */
	public function renderThumbnailOfElement(Element $file, $sourcePath = null) {
		return parent::renderThumbnail($sourcePath ?: $file->location, $file->getThumbLocation($this->profile));
	}
	
	/* returns whether the given file matches the requirements */
	public function renderChessboardOfAlbum(Album $album, Array $files) {
		return parent::renderChessboard($files, $album->getThumbLocation($this->profile));
	}
}


abstract class Element extends Component {
	
	/* unique path within the gallery */
	private $_galleryPath;
	
	/* reference to the parent album */
	private $_parent;
	
	/* absolute location on the filesystem */
	protected $location;
	
	/* absolute thumb location on the filesystem, containing "<profile>" which is replaced by the actual mode */
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
		$i = 1;
		do {
			$id = Lonely::simplifyString($name).($i++ > 1 ? '_'.$i : '');
		} while (in_array($id, self::$_usedIds));
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
	
	/* returns the absolute thumb file location pattern containing "<profile>" */
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
	public function getThumbLocation($profile) {
		return strtr($this->thumbLocationPattern, array('<profile>'=>$profile));
	}
	
	/* returns the web thumb path */
	public function getThumbPath($profile) {
		return ($this->thumbAvailable($profile) ? Lonely::model()->thumbPath : Lonely::model()->thumbScript).
			$profile.'/'.$this->path;
	}
	
	/* checks if there is a up-to-date thumbnail file */
	public function thumbAvailable($profile) {
		$thumbPath = $this->getThumbLocation($profile);
		return ($thumbPath && ($tTime = @filemtime($thumbPath)) && ($oTime = @filemtime($this->location)) && $tTime >= $oTime);
	}
	
	/* initializes the thumbnail */
	public function initThumb($profile) {
		/* check if thumbnail is available and up to date */
		return ($this->thumbAvailable($profile) || $this->createThumb($profile, $this->getThumbLocation($profile)));
	}
	
	/* creates a thumbnail */
	protected function createThumb($profile, $saveTo) {
		return false;
	}
	
	/* returns the mime type */
	public function getMime() {
		return 'application/octet-stream';
	}
	
	/* returns the HTML code for the thumbnail */
	public function getThumbHTML($profile) {
		$thumbpath = Lonely::escape($this->getThumbPath($profile));
		$name = Lonely::escape($this->getName());
		return "<img class=\"thumb\" src=\"".$thumbpath."\" alt=\"".$name."\">";
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
		$this->thumbLocationPattern = Lonely::model()->thumbDir.'<profile>'.DIRECTORY_SEPARATOR.(count($gPath) ? implode(DIRECTORY_SEPARATOR, $gPath).DIRECTORY_SEPARATOR : '');
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
	public function getThumbLocation($profile) {
		if ($thumbImage = $this->getThumbImage()) {
			return $thumbImage->getThumbLocation($profile);
		}
		return parent::getThumbLocation($profile).rawurlencode(Lonely::model()->albumThumb);
	}
	
	/* returns the web thumb path */
	public function getThumbPath($profile) {
		if ($thumbImage = $this->getThumbImage()) {
			return $thumbImage->getThumbPath($profile);
		}
		return parent::getThumbPath($profile).rawurlencode(Lonely::model()->albumThumb);
	}
	
	/* checks if there is a up-to-date thumbnail file */
	public function thumbAvailable($profile) {
		if ($thumbImage = $this->getThumbImage()) {
			return $thumbImage->initThumb($profile);
		}
		$thumbPath = $this->getThumbLocation($profile);
		return ($thumbPath && ($tTime = @filemtime($thumbPath)) &&
			($oTime = @filemtime($this->location)) && $tTime >= $oTime
			&& (!($oTime = @filemtime($this->location.Lonely::model()->albumThumbFile)) || $tTime >= $oTime)
		);
	}
	
	/* creates a thumbnail */
	protected function createThumb($profile, $saveTo) {
		
		/* number of images */
		$num = max(1, Lonely::model()->albumThumbSquare);
		$n = $num * $num;
		
		/* get images */
		$files = array();
		$design = Lonely::model()->getDesign();
		/* get files defined by the thumb file */
		if ($pathes = @file($this->location.Lonely::model()->albumThumbFile, FILE_SKIP_EMPTY_LINES)) {
			$numPathes = 0;
			foreach ($pathes as $path) {
				$file = Factory::createFileByRelPath(trim($path), $this);
				if ($file) {
					if ($file instanceof ContentFile) {
						$fileProfile = $design->thumbProfile($file);
						if ($file->initThumb($fileProfile)) {
							$files[] = $file->getThumbLocation($fileProfile);
							++$numPathes;
						}
					} else if ($thumb = $file->getThumbSourceLocation()) {
						$files[] = $thumb;
						++$numPathes;
					}
				}
			}
			$num = ceil(sqrt($numPathes));
			$n = $num * $num - $numPathes;
		}
		/* not enough? get files that are in the album */
		if ($n) {
			foreach($this->getFiles() as $file) {
				$path = null;
				if ($file instanceof ContentFile) {
					$fileProfile = $design->thumbProfile($file);
					if ($file->initThumb($fileProfile)) {
						$path = $file->getThumbLocation($fileProfile);
					}
				} else if ($thumb = $file->getThumbSourceLocation()) {
					$path = $thumb;
				}
				if (!in_array($path, $files)) {
					$files[] = $path;
					if (!--$n) {
						break;
					}
				}
			}
		}
		/* not enough? add albums */
		if ($n) {
			foreach($this->getAlbums() as $album) {
				if ($album->initThumb($profile)) {
					array_unshift($files, $album->getThumbLocation($profile));
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
		
		/* render */
		return RenderHelper::profile($profile)->renderChessboardOfAlbum($this, $files);
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
		$classname = Lonely::model()->albumClass;
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
	
	/* image from which to render the thumbnail */
	private $_thumbSourceLocation;
	
	/* whether to show the title */
	public $showTitle = false;
	
	
	function __destruct() {
		if ($this->_thumbSourceLocation) {
			unlink($this->_thumbSourceLocation);
		}
	}
	
	/* returns the source location of the image from which the thumbnail can be rendered */
	public function getThumbSourceLocation() {
		if ($this->_thumbSourceLocation === null) {
			$this->_thumbSourceLocation = $this->loadThumbSourceLocation();
		}
		return $this->_thumbSourceLocation;
	}
	
	/* loads the source location for the thumbnail */
	public function loadThumbSourceLocation() {
		return '';
	}
}

abstract class ContentFile extends File {

}

class Image extends ContentFile {
	
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
	
	/* returns the mime type */
	public function getMime() {
		$info = @getimagesize($this->location);
		return $info['mime'];
	}
	
	/* returns the HTML code for the preview */
	public function getPreviewHTML() {
		$path = empty(Lonely::model()->useOriginals) ? $this->getThumbPath(Lonely::model()->getDesign()->previewProfile($this)) : Lonely::model()->rootPath.$this->path;
		$name = Lonely::escape($this->getName());
		Lonely::model()->adjustMaxImageHeight = true;
		return "<img class=\"preview\" src=\"".Lonely::escape($path)."\" alt=\"".$name."\">\n";
	}
	
	/* returns whether this file is suitable as a thumb without resizing */
	public function canUseOriginalAsThumb($profile) {
		if (!isset($this->_useOriginalAsThumb[$profile])) {
			$this->_useOriginalAsThumb[$profile] = RenderHelper::profile($profile)->isSuitable($this->location);
		}
		return $this->_useOriginalAsThumb[$profile];
	}
	
	/* returns the absolute path of the thumbnail */
	public function getThumbLocation($profile) {
		return $this->canUseOriginalAsThumb($profile) ? $this->getLocation() : parent::getThumbLocation($profile);
	}
	
	/* returns the web thumb path */
	public function getThumbPath($profile) {
		return $this->canUseOriginalAsThumb($profile) ? Lonely::model()->rootPath.$this->path : parent::getThumbPath($profile);
	}
	
	/* checks if there is a up-to-date thumbnail file */
	public function thumbAvailable($profile) {
		return ($this->canUseOriginalAsThumb($profile) || parent::thumbAvailable($profile));
	}
	
	/* creates a thumbnail */
	protected function createThumb($profile, $saveTo) {
		return $this->canUseOriginalAsThumb($profile) || RenderHelper::profile($profile)->renderThumbnailOfElement($this);
	}
}

class GenericFile extends ContentFile {
	
	protected $thumbLocationPattern;
	protected $genericFileName = 'default.png';
	
	function __construct($gPath, $filename, Album $parent) {
		parent::__construct($gPath, $filename, $parent);
		
		if ($this->getFilename() !== "") {
			$this->thumbLocationPattern = Lonely::model()->thumbDir.'generic'.DIRECTORY_SEPARATOR.'<profile>'.DIRECTORY_SEPARATOR.$this->genericFileName;
		}
	}
	
	/* file pattern */
	public static function pattern() {
		return '/('.implode('|', Lonely::model()->extensions).')$/i';
	}
	
	/* returns the HTML code for the preview */
	public function getPreviewHTML() {
		$path = Lonely::escape($this->getThumbPath(Lonely::model()->getDesign()->previewProfile($this)));
		$name = Lonely::escape($this->getName());
		return "<img class=\"preview\" src=\"".$path."\" alt=\"".$name."\">";
	}
	
	/* returns the web thumb path */
	public function getThumbPath($profile) {
		return $this->thumbAvailable($profile) ? Lonely::model()->thumbPath.'generic/'.$profile.'/'.$this->genericFileName : Lonely::model()->thumbScript.$profile.'/'.$this->path;
	}
	
	/* checks if there is a up-to-date thumbnail file */
	public function thumbAvailable($profile) {
		$thumbPath = $this->getThumbLocation($profile);
		return ($thumbPath && is_file($thumbPath));
	}
	
	/* creates a thumbnail */
	protected function createThumb($profile, $saveTo) {
		$tmpfile = tempnam(sys_get_temp_dir(), 'lonely');
		return file_put_contents($tmpfile, base64_decode($this->base64EncodedThumbFile)) && RenderHelper::profile($profile)->renderThumbnailOfElement($this, $tmpfile) && unlink($tmpfile);
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
	public function cssFiles() {
		return array();
	}
	
	/* returns an array of thumbnail profiles */
	public function renderProfiles() {
		return array(
			'default/146px' => array(
				'width' => 146,
				'square' => true,
			),
			'default/300px' => array(
				'max-width' => 300,
				'square' => true,
			),
			'default/700px' => array(
				'max-width' =>700,
			),
		);
	}
	
	/* returns which profile to use for thumbnail of the given file/album */
	public function thumbProfile(Element $element) {
		return $element instanceof Album ? 'default/146px' : 'default/300px';
	}
	
	/* returns which profile to use for a preview of the given file */
	public function previewProfile(Element $element) {
		return 'default/700px';
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
		img[i].style.maxHeight = window.innerHeight + 'px';
	}
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
window.addEventListener('load', adjustMaxImageHeight);
window.addEventListener('resize', adjustMaxImageHeight);
window.addEventListener('keydown', navigate);
<?php
		exit;
	}
}
?>