<?php
/*
##########################
### Lonely PHP Gallery ###
##########################

### Version ###

1.1.0 beta 1
date: 2013-07-29

### Requirements ###

PHP 5.0.0 or above
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

Lonely PHP Gallery is a image gallery that aims to be simple in many ways: Its
functionality is limited to the basics you need to properly display and navigate
through your images and can be extended by modules if needed. Its design is
plain - the focus should lie on your images. Its implementation uses widely
supported and up to date standards like HTML5 and CSS3. Its configuration is
based on text files and does not require any PHP knowledge.

### First steps ###

Copy the PHP file to your webspace or directory containing image files. Sub
directories are interpreted as albums. You should not have albums with the names
'thumb' or 'config'. After visiting the web page for the first time these
directories will be created and serve a special purpose. The 'thumb' directory
will contain rendered previews to images or albums. You can savely delete this
directory every time to force a re-rendering of everything. The 'config'
directory will be empty in the beginning. You can add files here to customize
your gallery. For a start, try adding a simple text file 'title.txt' containing
something like 'My Awesome Gallery'. There are several other settings as well,
see section 'File based settings'.

### File based settings ###

There are a few different types of settings:
* Text: text file containing the value and with the extension '.txt'. E.g.
'name.txt'.
* On/off: empty file without extension. E.g. 'name' for on and '-name' for off.
* List: text file contining several lines representing several values and with
the extension '.list.txt'. E.g. 'names.list.txt'.

Settings can be overwritten in albums by creating a 'config' directory in the
album directory and place the setting file in it.

Settings can be disabled by appending a minus '-' in front of the file name.

Settings (only a subset):
* title
Title of the gallery. Use 'title.txt'.
* albumText
A text that is displayed on the main page and in every album. Use
'albumText.txt'.
* footer
A text that is displayed on every page below every other content. Use
'footer.txt'.

For advanced settings see below where the PHP code starts. A documentation will
be released soon.

### Modules ###

Modules are PHP files that can simply be placed in the 'config' directory. They
do not require to be activated in another way. Settings of a module can be set
like every other setting within the 'config' directory.

### Album previews ###

Album previews are rendered from the album's first 4 images and sub albums.
Repitition is used to fill up gaps. If you want to set your own album preview
image, place an image called '_album.jpg' in the album directory. If the preview
is not updated when switching between the two variants try deleting the 'thumb'
directory.

### PHP memory_limit ###

PHP's memory_limit parameter can break the rendering of bigger files if set to
low. Here is a table showing the relation between memory_limit and megapixels.
This table is the result of a short test and might be wrong.

memory_limit | megapixels
         16M | 2
         32M | 5
         64M | 10
        512M | 50
       1024M | 100

I recommend setting memory_limit to 64M if you are using a digital camera up to
10 megapixels and do not resize manually.

*/

// error_reporting(E_ALL);

/* you can make settings here */
$settings = array(
	
	/* file extensions of the files that should appear in the gallery */
	// 'extensions' => array('jpg', 'jpeg', 'png', 'gif'),
	
	/* gallery title */
	// 'title' => 'Lonely Gallery',
	
	/* META data */
	// 'metaDescription' => '',
	// 'metaKeywords' => '',
	// 'metaAuthor' => '',
	// 'metaRobots' => '',
	
	/* HTML to be displayed at the beginning of the album pages */
	// 'albumText' => '',
	
	/* HTML to be displayed at the end of the page */
	// 'footer' => '',
	
	/* quality of JPEG thumbnails */
	// 'JPEGQuality' => 80,
	
	/* compression of PNG thumbnails: 0 to 9 (max) */
	// 'PNGConpression' => 9,
	
	/* name of the thumbnail sub directory */
	// 'thumbDirectoryName' => 'thumb',
	
	/* name of the config sub directory */
	// 'configDirectoryName' => 'config',
	
	/* name of the thumb file of albums */
	// 'albumThumbName' => '_album.jpg',
	
	/* class name of the default design which is used if there is no design module */
	// 'defaultDesign' => 'DefaultLonelyDesign',
	
	/* default file action */
	// 'defaultFileAction' => 'preview',
	
	/* whether to use the original file rather than a rendered version on a preview page */
	// 'useOriginals' => false,
	
	/* css files to be loaded */
	// 'cssfiles' => array(),
	
	/* javascript files to be loaded */
	// 'jsfiles' => array(),
	
);

/* aaand ... action! */
$lonely = new LonelyGallery($settings);


/* base class for all lonely classes */
abstract class LonelyComponent {
	
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

class LonelyRequest extends LonelyComponent {
	
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
	/* scopes to match against */
	private static $scopes = array(
		array(self::MATCH_STRING, 'lonely'),
	);
	
	/* scope, defaults to 'lonely' */
	private $scope = array('lonely');
	/* album, defaults to none */
	private $album = array();
	/* file, defaults to none */
	private $file = '';
	/* action, defaults to 'index' */
	private $action = array('index');
	
	
	function __construct(Array $scopes) {
		
		/* get request string */
		$request = rawurldecode($_SERVER['REQUEST_URI']);
		if (strpos($request, $_SERVER['SCRIPT_NAME']) === 0) {
			/* cut off path and filename of this script */
			$request = substr($request, 1 + strlen($_SERVER['SCRIPT_NAME']));
		} else {
			/* cut off path of this script */
			$request = substr($request, 1 + strlen(dirname($_SERVER['SCRIPT_NAME'])));
		}
		
		/* convert to array and remove empty entries, then rebuild keys */
		$requestArray = array_values(array_diff(explode('/', $request), array('')));
		/* glue back together to have a clean string */
		$request = implode('/', $requestArray);
		
		/* match scope */
		foreach (array_merge(self::$scopes, $scopes) as $scope) {
			/* scope = array(0=>type, 1=>match string) */
			switch ($scope[0]) {
				case self::MATCH_STRING:
					/* match string */
					if (strpos($request, $scope[1]) === 0) {
						$this->scope = explode('/', $scope[1]);
						/* adjust request data */
						$request = substr($request, strlen($scope[1]) + 1);
						$requestArray = array_slice($requestArray, count($this->scope));
						/* stop matching */
						break 2;
					}
					break;
				case self::MATCH_REGEX:
					/* match regex */
					if (preg_match('#^('.$scope[1].')(/|$)#', $request, $match)) {
						$this->scope = explode('/', $match[1]);
						/* adjust request data */
						$request = substr($request, strlen($match[1]) + 1);
						$requestArray = array_slice($requestArray, count($this->scope));
						/* stop matching */
						break 2;
					}
					break;
			}
		}
		
		/* match album, file and action */
		/* search for the longest path that is a file or dir */
		for ($i = 0; $i <= count($requestArray); $i++) {
			
			$path = dirname(__FILE__).DIRECTORY_SEPARATOR.implode(DIRECTORY_SEPARATOR, array_slice($requestArray, 0, count($requestArray) - $i));
			
			/* check if file */
			if (@is_file($path)) {
				$pos = max(0, count($requestArray) - $i - 1);
				$this->album = array_slice($requestArray, 0, $pos);
				$this->file = $requestArray[$pos];
				if (count($requestArray) > ($pos + 1)) {
					$this->action = array_slice($requestArray, $pos + 1);
				}
				break;
			}
			if (@is_dir($path)) {
				$pos = count($requestArray) - $i;
				$this->album = array_slice($requestArray, 0, $pos);
				if (count($requestArray) > $pos) {
					$this->action = array_slice($requestArray, $pos);
				}
				break;
			}
			
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

class LonelyGallery extends LonelyComponent {
	
	/* file extensions of the files that should appear in the gallery */
	public $extensions = array('jpg', 'jpeg', 'png', 'gif');
	
	/* map file extension on class */
	public $extensionMap = array(
		'jpg' => 'LonelyImageFile',
		'jpeg' => 'LonelyImageFile',
		'png' => 'LonelyImageFile',
		'gif' => 'LonelyImageFile',
	);
	
	/* gallery title */
	public $title = 'Lonely Gallery';
	
	/* META data */
	public $metaDescription = '';
	public $metaKeywords = '';
	public $metaAuthor = '';
	public $metaRobots = '';
	
	/* HTML to be displayed at the beginning of the album pages */
	public $albumText = '';
	
	/* HTML to be displayed at the end of the page */
	public $footer = '';
	
	/* quality of JPEG thumbnails */
	public $JPEGQuality = 80;
	
	/* compression of PNG thumbnails: 0 to 9 (max) */
	public $PNGConpression = 9;
	
	/* name of the thumbnail sub directory */
	public $thumbDirectoryName = 'thumb';
	
	/* name of the config sub directory */
	public $configDirectoryName = 'config';
	
	/* name of the thumb file of albums */
	public $albumThumbName = '_album.jpg';
	
	/* album class to use */
	public $albumClass = 'LonelyAlbum';
	
	/* class name of the default design which is used if there is no design module */
	public $defaultDesign = 'DefaultLonelyDesign';
	
	/* default file action */
	public $defaultFileAction = 'preview';
	
	/* whether to use the original file rather than a rendered version on a preview page */
	public $useOriginals = false;
	
	/* css files to be loaded */
	public $cssfiles = array();
	
	/* javascript files to be loaded */
	public $jsfiles = array();
	
	/* modules */
	private $modules = array();
	private $modulesInitialized = false;
	
	/* design */
	private $design = null;
	
	/* full host adress, like: https://sub.example.org:8080 */
	private $server = '';
	
	/* start time */
	private $startTime = 0;
	
	
	function __construct(Array $settings = array()) {
		
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
		$this->rootDir = dirname(__FILE__).DIRECTORY_SEPARATOR;
		
		/* set default design */
		if ($this->defaultDesign) {
			$this->addModule($this->defaultDesign);
		}
		
		/* check for GD */
		if (!function_exists('gd_info')) {
			$this->error(500, 'Error 500: Missing GD library. Make sure your PHP installation includes the GD library.');
		}
		
		/* config directory */
		$this->configDir = $this->rootDir.$this->configDirectoryName.DIRECTORY_SEPARATOR;
		if (!is_dir($this->configDir)) {
			if (!mkdir($this->configDir)) {
				$this->error(500, 'Error 500: Config directory (/'.$this->configDirectoryName.') could not be created. Check if your user has permission to write to your gallery directory.');
			}
		}
		
		/* read data from config dir */
		if (is_readable($this->configDir) && is_executable($this->configDir)) {
			$this->readConfig($this->configDir);
		} else {
			if (!is_readable($this->configDir)) {
				$this->error(500, 'Error 500: Config directory (/'.$this->configDirectoryName.') is not readable.');
			} else {
				$this->error(500, 'Error 500: Config directory (/'.$this->configDirectoryName.') cannot be entered due to missing executing rights.');
			}
		}
		
		/* render directory */
		$this->thumbDir = $this->rootDir.$this->thumbDirectoryName.DIRECTORY_SEPARATOR;
		if (!is_dir($this->thumbDir)) {
			if (!mkdir($this->thumbDir)) {
				$this->error(500, 'Error 500: Thumbnail directory (/'.$this->thumbDirectoryName.') could not be created. Check if your user has permission to write to your gallery directory.');
			}
			if (!is_readable($this->thumbDir)) {
				$this->error(500, 'Error 500: Thumbnail directory (/'.$this->thumbDirectoryName.') is not readable.');
			}
		}
		
		/* the gallery's full URL */
		$this->server = 'http'.(empty($_SERVER['HTTPS']) ? '' : 's').'://' // scheme
				.$_SERVER['SERVER_NAME'] // domain
				.((($_SERVER['SERVER_PORT'] == 80 && empty($_SERVER['HTTPS'])) || ($_SERVER['SERVER_PORT'] == 443 && !empty($_SERVER['HTTPS']))) ? '' : ':'.$_SERVER['SERVER_PORT']); // port
		/* root-relative path */
		$this->rootPath = dirname($_SERVER['SCRIPT_NAME']).'/';
		$this->rootScript = $_SERVER['SCRIPT_NAME'].'?/';
		$this->thumbPath = $this->rootPath.$this->thumbDirectoryName.'/';
		$this->thumbScript = $this->rootScript.$this->thumbDirectoryName.'/';
		$this->configPath = $this->rootPath.$this->configDirectoryName.'/';
		$this->configScript = $this->rootScript.$this->configDirectoryName.'/';
		
		/* initialize file factory */
		LonelyFileFactory::init($this);
		
		/* init request */
		$scopes = array(
			array(LonelyRequest::MATCH_STRING, $this->configDirectoryName),
			array(LonelyRequest::MATCH_REGEX, $this->thumbDirectoryName.'/[0-9]+(px|sq)'),
		);
		$this->request = new LonelyRequest($scopes);
		$album = $this->request->album;
		
		/* check album, must not be config or thumbnail directory */
		if (count($album) && ($this->thumbDirectoryName == $album[0] || in_array($this->configDirectoryName, $album))) {
			$this->error();
		}
		
		/* check for hidden files and directories */
		$file = $this->request->file;
		if ($file && $this->isHiddenFileName($file) && $file != $this->albumThumbName) {
			$this->error();
		}
		foreach ($album as $a) {
			if ($a && $this->isHiddenAlbumName($a)) {
				$this->error();
			}
		}
		
		/* read data from album config dir */
		for ($n = 1; $n <= count($album); $n++) {
			$dir = $this->rootDir.implode(DIRECTORY_SEPARATOR, array_slice($album, 0, $n)).DIRECTORY_SEPARATOR.$this->configDirectoryName.DIRECTORY_SEPARATOR;
			if (is_readable($dir) && is_executable($dir)) {
				$this->readConfig($dir);
			}
		}
		
		/* initialize modules */
		$this->initModules();
		
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
			
			/* skip hidden (./../.htaccess) and deactivated files (begin with '-') */
			if ($file[0] == '.' || !is_file($dir.$file)) {
				continue;
			}
			
			/* turn off setting */
			else if ($file[0] == '-') {
				if (substr($file, -12) == "LonelyModule") {
					$this->removeModule(substr($file, 1));
				} else {
					$this->{substr($file, 1)} = false;
				}
			}
			
			/* modules always end like this */
			else if (substr($file, -16) == "LonelyModule.php") {
				$this->addModule(substr($file, 0, -4));
			}
			
			/* value list */
			else if (substr($file, -9) == ".list.txt") {
				$value = array();
				foreach (file($dir.$file, FILE_SKIP_EMPTY_LINES|FILE_IGNORE_NEW_LINES) as $line) {
					$value[] = $line;
				}
				$this->{substr($file, 0, -9)} = $value;
			}
			
			/* settings with a value */
			else if (substr($file, -4) == ".txt") {
				$this->{substr($file, 0, -4)} = trim(file_get_contents($dir.$file));
			}
			
			/* otherwise it is a turn on setting */
			else {
				$this->{$file} = true;
			}
			
		}
	}
	
	/* handle request */
	public function handleRequest(LonelyRequest $request) {
		
		/* let modules handle the request */
		foreach ($this->modules as $module) {
			if (method_exists($module, 'handleRequest')) {
				$goon = call_user_func(array($module, 'handleRequest'), $request);
				if (!$goon) {
					return;
				}
			}
		}
		
		$scope = $request->scope;
		$action = $request->action;
		
		switch ($scope[0]) {
			
			/* thumb */
			case $this->thumbDirectoryName:
				$method = $scope[0];
				foreach (array_slice($scope, 1) as $scope) {
					$method .= ucfirst($scope);
				}
				$method .= 'Action';
				break;
			
			/* config */
			case $this->configDirectoryName:
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
				if ($request->file == $this->albumThumbName) {
					$this->error();
				}
				$method = $scope[0];
				foreach (array_slice($scope, 1) as $scope) {
					$method .= ucfirst($scope);
				}
				$method .= ucfirst(preg_replace('#[^-\w\d]#', '_', $action[0])).'Action';
			
		}
		// var_dump($method); exit;
		
		/* bring the action */
		if (method_exists($this, $method)) {
			call_user_func(array($this, $method), $this->request);
			return;
		}
		foreach ($this->modules as $module) {
			if (method_exists($module, $method)) {
				call_user_func(array($module, $method), $this->request);
				return;
			}
		}
		
		/* nothing called */
		$this->error();
	}
	
	/* evaluates if the file or dir name is hidden */
	public function isHiddenName($name) {
		return $name && ($name[0] == '.' || $name[0] == '-');
	}
	
	/* evaluates if the file name is hidden */
	public function isHiddenFileName($name) {
		return $name && ($name[0] == '.' || $name[0] == '-' || $name == $this->albumThumbName);
	}
	
	/* evaluates if the dir name is hidden */
	public function isHiddenAlbumName($name) {
		return $name && ($name[0] == '.' || $name[0] == '-' || $name == $this->configDirectoryName || $name == $this->thumbDirectoryName);
	}
	
	/* add module */
	public function addModule($modules) {
		foreach ((array)$modules as $module) {
			$this->modules[$module] = null;
		}
	}
	
	/* remove module */
	public function removeModule($modules) {
		foreach ((array)$modules as $module) {
			if (array_key_exists($module, $this->modules)) {
				unset($this->modules[$module]);
			}
		}
	}
	
	/* returns the list of modules */
	public function getModules() {
		return $this->modules;
	}
	
	/* initializes the modules */
	public function initModules() {
		
		/* first load all files to prevent missing requirements */
		foreach ($this->modules as $name => &$module) {
			if (!class_exists($name)) {
				require_once($this->configDir.$name.'.php');
			}
		}
		
		/* init objects */
		foreach ($this->modules as $name => &$module) {
			/* initialize module */
			$module = new $name($this);
			/* fetch settings from module */
			$this->set($module->settings());
			/* switch designs */
			if ($module instanceof LonelyDesign) {
				$this->design = $module;
			}
		}
		
		$this->modulesInitialized = true;
		
	}
	
	/* calls an event */
	public function callEvent($eventName) {
		$method = $eventName.'Event';
		$data = array();
		foreach ($this->modules as $moduleName => $module) {
			if (method_exists($module, $method)) {
				$data[$moduleName] = call_user_func_array(array($module, $method), array_slice(func_get_args(), 1));
			}
		}
		return $data;
	}
	
	/* add file extensions */
	public function setExtensions($value) {
		if (!is_array($value)) {
			preg_match_all('#[a-z]+#i', $value, $matches);
			$value = $matches[0];
		}
		foreach ($value as $v) {
			$this->data['extensions'][] = strtolower($v);
		}
	}
	
	/* map file extensions to class */
	public function addExtensionmap(Array $map) {
		$this->extensionmap = array_merge($this->extensionmap, $map);
	}
	
	/* css files to include */
	public function addCssfile($files) {
		$this->cssfiles = array_merge($this->cssfiles, (array)$files);
	}
	
	/* JavaScript files to include */
	public function addJsfile($files) {
		$this->jsfiles = array_merge($this->jsfiles, (array)$files);
	}
	
	/* show album or image */
	protected function lonelyIndexAction(LonelyRequest $request) {
		
		$album = new $this->albumClass($this, $request->album);
		$file = LonelyFileFactory::create($request->file, $album);
		
		/* file requested */
		if ($file && $file->isAvailable()) {
			
			/* redirect to the file */
			header('Location: '.$this->server.$this->rootPath.$file->getPath(), true, 301);
			exit;
			
		}
		
		/* album requested */
		else if ($album->isAvailable()) {
			
			$html = $this->albumText."\n";
			
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
				$html .= "<nav class=\"breadcrumbs\">\n";
				$html .= "\t<ul>\n";
				foreach (array_reverse($parents) as $element) {
					$html .= "\t\t<li><a href=\"".$this->escape($this->rootScript.$element->getPath())."\">".$this->escape($element->getName())."</a></li>\n";
				}
				$html .= "\t\t<li>".$this->escape($album->getName())."</li>\n";
				$html .= "\t</ul>\n";
				$html .= "</nav>\n\n";
			}
			
			/* albums */
			$mode = '150sq';
			if (count($albums = $album->getAlbums())) {
				$html .= "<ul id=\"albums\">\n";
				foreach ($albums as $element) {
					$path = $this->escape($this->rootScript.$element->getPath());
					$thumbpath = $this->escape($element->getThumbPath($mode));
					$name = $this->escape($element->getName());
					$html .= "\t<li>\n";
					$html .= "\t\t<img src=\"".$thumbpath."\" alt=\"".$name."\">\n";
					$html .= "\t\t<a href=\"".$path."\"><span>".$name."</span></a>\n";
					$html .= "\t</li>\n";
				}
				$html .= "</ul>\n\n";
			}
			
			/* files */
			$mode = '300sq';
			$action = $this->defaultFileAction;
			if (count($files = $album->getFiles())) {
				$html .= "<ul id=\"images\">\n";
				foreach ($files as $element) {
					$path = $this->escape($this->rootScript.$element->getPath().'/'.$action);
					$thumbpath = $this->escape($element->getThumbPath($mode));
					$name = $this->escape($element->getName());
					$html .= "\t<li>\n";
					$html .= "\t\t<img src=\"".$thumbpath."\" alt=\"".$name."\">\n";
					$html .= "\t\t<a href=\"".$path."\"><span>".$name."</span></a>\n";
					$html .= "\t</li>\n";
				}
				$html .= "</ul>\n\n";
			}
			
			/* empty album */
			if (!count($albums) && !count($files)) {
				if (empty($request->album)) {
					$html .= "<p>This gallery is empty. Try adding some image files to the directory you placed this script in. You can also have albums by creating directories.</p>";
				} else {
					$html .= "<p>This album is empty.</p>";
				}
			}
			
			$this->HTMLContent = $html;
			$this->display();
			
		}
		
		/* nothing requested */
		else {
			$this->error();
		}
		
	}
	
	/* display thumb page */
	protected function lonelyPreviewAction(LonelyRequest $request) {
		
		$album = new $this->albumClass($this, $request->album);
		$file = LonelyFileFactory::create($request->file, $album);
		
		/* file requested */
		if ($file && $file->isAvailable()) {
			
			$html = "";
			$name = $this->escape($file->getName());
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
				$html .= "<nav class=\"breadcrumbs\">\n";
				$html .= "\t<ul>\n";
				foreach (array_reverse($parents) as $element) {
					$html .= "\t\t<li><a href=\"".$this->escape($this->rootScript.$element->getPath())."\">".$this->escape($element->getName())."</a></li>\n";
				}
				$html .= "\t\t<li>".$name."</li>\n";
				$html .= "\t</ul>\n";
				$html .= "</nav>\n\n";
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
				$html .= "<nav class=\"imagenav\">\n";
				$html .= "\t<p>\n";
				$html .= "\t\t".($first ? "<a href=\"".$this->escape($this->rootScript.$first->getPath().'/'.$action)."\">first</a>" : "<span>first</span>")."\n";
				$html .= "\t\t".($prev ? "<a rel=\"prev\" href=\"".$this->escape($this->rootScript.$prev->getPath().'/'.$action)."\">previous</a>" : "<span>previous</span>")."\n";
				$html .= "\t\t".($next ? "<a rel=\"next\" href=\"".$this->escape($this->rootScript.$next->getPath().'/'.$action)."\">next</a>" : "<span>next</span>")."\n";
				$html .= "\t\t".($last ? "<a href=\"".$this->escape($this->rootScript.$last->getPath().'/'.$action)."\">last</a>" : "<span>last</span>")."\n";
				$html .= "\t</p>\n";
				$html .= "</nav>\n\n";
			}
			
			/* image */
			$html .= "<div class=\"image\">\n";
			
			$html .= "\t<div class=\"image-box\">\n";
			$html .= "\t\t".$file->getPreviewHTML()."\n";
			if ($prev) {
				$html .= "\t\t<a class=\"prev\" rel=\"prev\" href=\"".$this->escape($this->rootScript.$prev->getPath().'/'.$action)."\"></a>\n";
			}
			if ($next) {
				$html .= "\t\t<a class=\"next\" rel=\"next\" href=\"".$this->escape($this->rootScript.$next->getPath().'/'.$action)."\"></a>\n";
			}
			$html .= "\t</div>\n\n";
			
			/* info */
			$html .= "\t<div class=\"image-info\">\n";
			$html .= "\t\t<p class=\"title\">".$name."</p>\n";
			$html .= "\t\t<p class=\"download\"><a href=\"".$this->escape($this->rootPath.$file->getPath())."\">Download</a></p>\n";
			$dlOpen = false;
			foreach ($this->callEvent('fileInfo', $file) as $data) {
				foreach ($data as $key => $value) {
					if (is_int($key)) {
						if ($dlOpen) {
							$html .= "\t\t</dl>\n";
							$dlOpen = false;
						}
						$html .= "\t\t<p>".$this->escape($value)."</p>\n";
					} else {
						if (!$dlOpen) {
							$html .= "\t\t<dl>\n";
							$dlOpen = true;
						}
						$html .= "\t\t\t<dt>".$this->escape($key)."</dt>\n";
						$html .= "\t\t\t<dd>".$this->escape($value)."</dd>\n";
					}
				}
			}
			if ($dlOpen) {
				$html .= "\t\t</dl>\n";
			}
			$html .= "\t</div>\n";
			
			$html .= "</div>\n\n";
			
			$this->HTMLContent = $html;
			$this->display();
			
		} else {
			$this->error();
		}
		
	}
	
	/* shows the thumbnail */
	protected function displayThumb(LonelyRequest $request, $mode) {
		
		$element = $album = new $this->albumClass($this, $request->album);
		if ($request->file) {
			$element = LonelyFileFactory::create($request->file, $album);
		} else if ($request->action[0] == $this->albumThumbName) {
			$element = LonelyFileFactory::create($request->action[0], $album);
		}
		
		if (!$element->isAvailable()) {
			if ($album->isAvailable()) {
				$element = $album;
			} else {
				$this->error();
			}
		}
		
		if ($element->initThumb($mode)) {
			
			/* redirect to thumbnail */
			header("Location: ".$element->getThumbPath($mode));
		
			// $mime = $element->getMime();
			// $path = $element->getThumbLocation($mode);
			
			// $lastmodified = filemtime($path);
			// if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE']) && $lastmodified && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $lastmodified) {
				// header("HTTP/1.1 304 Not Modified", true, 304);
			// } else {
				// header("Last-Modified: ".date(DATE_RFC1123, $lastmodified));
				// header('Content-Type: '.$mime);
				// readfile($path);
			// }
			exit;
		
		} else {
			$this->error(500, 'Could not calculate Thumbnail.');
		}
		
	}
	
	/* shows 150px square thumbnail */
	protected function thumb150sqAction(LonelyRequest $request) {
		$this->displayThumb($request, '150sq');
	}
	
	/* shows 300px square thumbnail */
	protected function thumb300sqAction(LonelyRequest $request) {
		$this->displayThumb($request, '300sq');
	}
	
	/* shows 700px thumbnail */
	protected function thumb700pxAction(LonelyRequest $request) {
		$this->displayThumb($request, '700px');
	}
	
	/* show an error page */
	protected function error($errno = 404, $message = "The page you were looking for was not found.") {
		
		/* because this method can be called early, try to init modules */
		if (!$this->modulesInitialized) {
			try {
				$this->initModules();
			} catch (Exception $e) {
				/* reset all modules */
				$this->modules = array();
			}
		}
		
		if (function_exists('http_response_code')) {
			http_response_code($errno);
		} else {
			header(' ', true, $errno);
		}
		$this->HTMLTitle = "Error ".$errno." - ".$this->title;
		$this->HTMLContent = "<p class=\"error\">".$this->escape($message)."</p>\n\n";
		$this->display();
		exit;
	}
	
	/* HTML escape */
	public function escape($string) {
		$text = htmlentities(@iconv('UTF-8', 'UTF-8//IGNORE', $string), ENT_QUOTES, 'UTF-8');
		/* umlauts workaround, https://bugs.php.net/bug.php?id=61484 */
		if ($text == '' && $string != '') {
			return preg_replace('#[^-_\w\d ]#', '_', $string);
		}
		return $text;
	}
	
	/* displays the page */
	protected function display() {
		?><!DOCTYPE html>
<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<?php
		
		/* optional meta information */
		if (($m = $this->metaDescription) != "") {
			echo "\t<meta name=\"description\" content=\"", $this->escape($m), "\">\n";
		}
		if (($m = $this->metaKeywords) != "") {
			echo "\t<meta name=\"keywords\" content=\"", $this->escape($m), "\">\n";
		}
		if (($m = $this->metaAuthor) != "") {
			echo "\t<meta name=\"author\" content=\"", $this->escape($m), "\">\n";
		}
		if (($m = $this->metaRobots) != "") {
			echo "\t<meta name=\"robots\" content=\"", $this->escape($m), "\">\n";
		}
		
		/* CSS */
		$cssfiles = array_merge($this->design->getCSSFiles(), $this->cssfiles);
		foreach ($cssfiles as $file) {
			echo "\t<link type=\"text/css\" rel=\"stylesheet\" media=\"screen\" href=\"", $this->escape($file), "\">\n";
		}
		
		/* JavaScript */
		foreach ($this->jsfiles as $file) {
			echo "\t<script type=\"text/javascript\" src=\"", $this->escape($file), "\">\n\n";
		}
		
		/* page title */
		echo "\t<title>", $this->escape($this->HTMLTitle ? $this->HTMLTitle : $this->title), "</title>\n";
	
	
	?></head>
<body>
	
	<h1><a href="<?php echo $this->escape($this->rootScript); ?>"><?php echo $this->escape($this->title); ?></a></h1>

	<div id="content">

		<?php echo str_replace("\n", "\n\t\t", $this->HTMLContent); ?>
	
	</div>
	
	<?php echo $this->footer; ?>
	
<!-- execution: <?php echo round((microtime(true) - $this->startTime) * 1000, 3); ?> ms -->

</body>
</html><?php
		
	}
}

abstract class LonelyElement extends LonelyComponent {
	
	/* reference to the main class */
	protected $lonely;
	
	/* reference to the parent album if there is one */
	protected $parentAlbum;
	
	/* absolute location on the filesystem */
	protected $location;
	
	/* absolute thumb location on the filesystem, containing "<mode>" which is replaced by the actual mode */
	protected $thumbLocationPattern;
	
	/* relative web path */
	protected $path;
	
	
	function __construct(LonelyGallery $lonely, LonelyAlbum $parentAlbum = null) {
		$this->lonely = $lonely;
		$this->parentAlbum = $parentAlbum;
	}
	
	/* check if the file or directory is available */
	function isAvailable() {
		return @is_readable($this->location);
	}
	
	/* returns the object of the parent album */
	public function getParent() {
		return $this->parentAlbum;
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
		return ($this->thumbAvailable($mode) ? $this->lonely->thumbPath : $this->lonely->thumbScript).
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
}

class LonelyAlbum extends LonelyElement {
	
	/* array containing the album path */
	protected $album;
	
	/* albums and files in this album */
	protected $albums;
	protected $files;
	
	
	function __construct(LonelyGallery $lonely, Array $album = array(), self $parentAlbum = null) {
		$this->album = $album;
		parent::__construct($lonely, $parentAlbum);
		
		$this->location = $this->lonely->rootDir.(count($this->album) ? implode(DIRECTORY_SEPARATOR, $this->album).DIRECTORY_SEPARATOR : '');
		$this->thumbLocationPattern = $this->lonely->thumbDir.'<mode>'.DIRECTORY_SEPARATOR.(count($this->album) ? implode(DIRECTORY_SEPARATOR, $this->album).DIRECTORY_SEPARATOR : '');
		$this->path = count($this->album) ? implode('/', array_map('rawurlencode', $this->album)).'/' : '';
	}
	
	/* return the name of this element */
	public function getName() {
		$name = count($this->album) ? end($this->album) : $this->lonely->title;
		$name = str_replace('_', ' ', $name);
		return $name;
	}
	
	/* returns the object of the parent album */
	public function getParent() {
		if ($this->parentAlbum === null && count($this->album) > 0) {
			$this->parentAlbum = new self($this->lonely, array_slice($this->album, 0, -1));
		}
		return $this->parentAlbum;
	}
	
	/* reads in the files and dirs within the directory of this album */
	public function loadElements() {
		
		$this->albums = array();
		$this->files = array();
		
		/* this is clean if this is a subdirectory which is not the config or thumb directory */
		// $cleanLocation = count($this->album) && $this->album[0] !== $this->lonely->configDir && $this->album[0] !== $this->lonely->thumbDir;
		$cleanLocation = count($this->album) && !in_array($this->lonely->configDirectoryName, $this->album) && $this->album[0] !== $this->lonely->thumbDirectoryName;
		
		/* go through each element */
		$dir = opendir($this->location);
		while (($filename = readdir($dir)) !== false) {
			
			/* skip files starting with a dot or a minus */
			// if ($filename[0] == '.' || $filename[0] == '-') {
			if ($this->lonely->isHiddenName($filename)) {
				continue;
			}
			
			/* get location */
			$location = $this->location.$filename;
			
			/* the element must not be in the config or thumb directory */
			if (!$cleanLocation && (strpos($location.DIRECTORY_SEPARATOR, $this->lonely->configDir) === 0 || strpos($location.DIRECTORY_SEPARATOR, $this->lonely->thumbDir) === 0)) {
				continue;
			}
			
			/* check if link */
			// if (is_link($location)) {
				// /* change location to the linked location */
				// $location = readlink($location);
				// /* skip if new location is not within the gallery's root directory */
				// if (strpos($location.DIRECTORY_SEPARATOR, $this->lonely->rootDir) !== 0) {
					// continue;
				// }
			// }
			
			switch (filetype($location)) {
				
				case 'dir':
					/* must not be config directory */
					// if ($filename !== $this->lonely->configDirectoryName) {
					if (!$this->lonely->isHiddenAlbumName($filename)) {
						$album = new self($this->lonely, array_merge($this->album, array($filename)), $this);
						$this->albums[$filename] = $album;
					}
					break;
				
				case 'file':
					if (!$this->lonely->isHiddenFileName($filename)) {
						$file = LonelyFileFactory::create($filename, $this);
						if ($file) {
							$this->files[$filename] = $file;
						}
					}
					break;
				
			}
			
		}
		
		/* sort alphabetically */
		ksort($this->albums);
		ksort($this->files);
		
	}
	
	/* returns the array of albums in this album */
	public function getAlbums() {
		if ($this->albums === null) {
			$this->loadElements();
		}
		return $this->albums;
	}
	
	/* returns the array of files in this album */
	public function getFiles() {
		if ($this->files === null) {
			$this->loadElements();
		}
		return $this->files;
	}
	
	/* returns the absolute path of the thumbnail */
	public function getThumbLocation($mode) {
		return parent::getThumbLocation($mode).rawurlencode($this->lonely->albumThumbName);
	}
	
	/* returns the web thumb path */
	public function getThumbPath($mode) {
		return parent::getThumbPath($mode).rawurlencode($this->lonely->albumThumbName);
	}
	
	/* checks if there is a up-to-date thumbnail file */
	public function thumbAvailable($mode) {
		$albumFile = $this->location.$this->lonely->albumThumbName;
		/* check manual album image if exists */
		if (is_file($albumFile)) {
			$thumbPath = $this->getThumbLocation($mode);
			return ($thumbPath && ($tTime = @filemtime($thumbPath)) && ($oTime = @filemtime($albumFile)) && $tTime >= $oTime);
		} else {
			return parent::thumbAvailable($mode);
		}
	}
	
	/* creates a thumbnail */
	protected function createThumb($mode, $saveTo) {
		
		/* mode to use for files to not render needless versions of the images used in the album thumbnail */
		switch ($mode) {
			case '150sq':
			case '300sq':
			default: $fileMode = '300sq';
		}
		
		/* get 4 images */
		$files = array();
		$count = 0;
		foreach($this->getFiles() as $file) {
			if ($file->initThumb($fileMode)) {
				$files[] = $file->getThumbLocation($fileMode);
				$count++;
			}
			if ($count >= 4) {
				break;
			}
		}
		/* not enough? add albums */
		if ($count < 4) {
			foreach($this->getAlbums() as $album) {
				if ($album->initThumb($mode)) {
					array_unshift($files, $album->getThumbLocation($mode));
					$count++;
				}
				if ($count >= 4) {
					break;
				}
			}
		}
		/* not enough? add duplicates */
		if ($count && $count < 4) {
			for ($i = 3, $a = 0; $i >= 0 && !isset($files[$i]); $i--) {
				if (!isset($files[$a])) {
					$a = 0;
				}
				$files[$i] = $files[$a++];
				$count++;
			}
		}
		
		/* create new image */
		switch ($mode) {
			case '150sq': $thumb = imagecreatetruecolor(150, 150); break;
			case '300sq': $thumb = imagecreatetruecolor(300, 300); break;
			default: return false;
		}
		
		/* transparency for gif and png */
		// if (in_array($info[2], array(IMAGETYPE_GIF, IMAGETYPE_PNG))) {
			// $transparent = imagecolorallocatealpha($thumb, 0, 0, 0, 127);
			// imagecolortransparent($thumb, $transparent);
			// imagefill($thumb, 0, 0, $transparent);
			// imagealphablending($thumb, false);
			// imagesavealpha($thumb, true);
		// }
		
		/* modes */
		$square = false;
		$upscaling = false;
		switch ($mode) {
			case '150sq': $square = true; $maxWidth = $maxHeight = 75; break;
			case '300sq': $square = true; $maxWidth = $maxHeight = 150; break;
			default: return false;
		}
		
		/* go through files and add them to the thumbnail */
		$nr = 0;
		foreach ($files as $file) {
			
			/* get info */
			$info = getimagesize($file);
			
			/* calculate dimensions */
			
			$imageWidth = $info[0];
			$imageHeight = $info[1];
			$imageX = $imageY = 0;
			
			/* square mode */
			if ($square) {
				
				$thumbWidth = $maxWidth;
				$thumbHeight = $maxHeight;
				
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
				if (!$upscaling && $imageWidth < $maxWidth && $imageHeight < $maxHeight) {
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
				case IMAGETYPE_GIF: $image = imagecreatefromgif($file); break;
				case IMAGETYPE_JPEG: $image = imagecreatefromjpeg($file); break;
				case IMAGETYPE_PNG: $image = imagecreatefrompng($file); break;
				case IMAGETYPE_WBMP: $image = imagecreatefromwbmp($file); break;
				default: return false;
			}
			
			/* resize */
			switch ($nr) {
				case 0: $toX = 0; $toY = 0; break;
				case 1: $toX = $maxWidth; $toY = 0; break;
				case 2: $toX = 0; $toY = $maxHeight; break;
				case 3: $toX = $maxWidth; $toY = $maxHeight; break;
				default: return false;
			}
			imagecopyresampled($thumb, $image, $toX, $toY, $imageX, $imageY, $thumbWidth, $thumbHeight, $imageWidth, $imageHeight);
			
			imagedestroy($image);
			
			$nr++;
		}
		
		/* save */
		
		/* create dir */
		$dir = dirname($saveTo);
		if (!is_dir($dir)) {
			mkdir($dir, 0777, true);
		}
		
		/* write to file */
		// switch ($info[2]) {
			// case IMAGETYPE_GIF: return imagegif($thumb, $saveTo);
			// case IMAGETYPE_JPEG: 
			// case IMAGETYPE_PNG: return imagepng($thumb, $saveTo, $this->lonely->PNGConpression);
			// case IMAGETYPE_WBMP: return imagewbmp($thumb, $saveTo);
		// }
		return imagejpeg($thumb, $saveTo, $this->lonely->JPEGQuality);
		// return false;
	}
}

abstract class LonelyFileFactory {
	
	private static $lonely;
	
	public static function init(LonelyGallery $lonely) {
		self::$lonely = $lonely;
	}
	
	/* returns the instance of the object or null if not supported */
	public static function create($filename, LonelyAlbum $parentAlbum) {
		$extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
		if (!in_array($extension, self::$lonely->extensions)) {
			return null;
		}
		$extensionMap = self::$lonely->extensionMap;
		if (isset($extensionMap[$extension])) {
			return new $extensionMap[$extension](self::$lonely, $filename, $parentAlbum);
		}
		return new LonelyGenericFile(self::$lonely, $filename, $parentAlbum);
	}
}

abstract class LonelyFile extends LonelyElement {
	
	/* filename on the file system */
	protected $filename;
	
	
	function __construct(LonelyGallery $lonely, $filename, LonelyAlbum $parentAlbum) {
		$this->filename = $filename;
		parent::__construct($lonely, $parentAlbum);
		
		if ($this->filename !== "") {
			$this->location = $this->parentAlbum->getLocation().$this->filename;
			$this->thumbLocationPattern = $this->parentAlbum->getThumbPathPattern().$this->filename;
			$this->path = $this->parentAlbum->getPath().rawurlencode($this->filename);
		}
	}
	
	/* returns the filename */
	public function getFilename() {
		return $this->filename;
	}
	
	/* return the name of this element */
	public function getName() {
		$name = substr($this->filename, 0, strrpos($this->filename, '.'));
		$name = str_replace('_', ' ', $name);
		return $name;
	}
	
	/* returns the HTML code for the preview */
	public function getPreviewHTML() {
		return "";
	}
}

class LonelyImageFile extends LonelyFile {
	
	protected $imageInfo;
	protected $useOriginalAsThumb = array();
	
	/* returns the image info */
	public function getImageInfo() {
		if (!$this->imageInfo) {
			$this->imageInfo = getimagesize($this->location);
		}
		return $this->imageInfo;
	}
	
	/* returns the mime type */
	public function getMime() {
		$info = $this->getImageInfo();
		return $info['mime'];
	}
	
	/* returns the HTML code for the preview */
	public function getPreviewHTML() {
		$path = empty($this->lonely->useOriginals) ? $this->getThumbPath('700px') : $this->getPath();
		$name = $this->lonely->escape($this->getName());
		return "<img src=\"".$this->lonely->escape($path)."\" alt=\"".$name."\">";
	}
	
	/* returns whether this file is suitable as a thumb without resizing */
	public function canUseOriginalAsThumb($mode) {
		if (!isset($this->useOriginalAsThumb[$mode])) {
			/* evaluate whether this file has to be resized */
			
			/* get info */
			$info = $this->getImageInfo();
			
			switch ($mode) {
				case '150sq': $v = ($info[0] == $info[1] && $info[0] <= 150); break;
				case '300sq': $v = ($info[0] == $info[1] && $info[0] <= 300); break;
				case '700px': $v = ($info[0] <= 700 && $info[1] <= 700); break;
				default: $v = false;
			}
			$this->useOriginalAsThumb[$mode] = $v;
		}
		return $this->useOriginalAsThumb[$mode];
	}
	
	/* returns the absolute path of the thumbnail */
	public function getThumbLocation($mode) {
		return $this->canUseOriginalAsThumb($mode) ? $this->getLocation() : parent::getThumbLocation($mode);
	}
	
	/* returns the web thumb path */
	public function getThumbPath($mode) {
		return $this->canUseOriginalAsThumb($mode) ? $this->lonely->rootPath.$this->getPath() : parent::getThumbPath($mode);
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
		$square = false;
		$upscaling = false;
		switch ($mode) {
			case '150sq': $square = true; $maxWidth = $maxHeight = 150; break;
			case '300sq': $square = true; $maxWidth = $maxHeight = 300; break;
			case '700px': $maxWidth = $maxHeight = 700; break;
			default: return false;
		}
		
		/* calculate dimensions */
		
		$imageWidth = $info[0];
		$imageHeight = $info[1];
		$imageX = $imageY = 0;
		
		/* square mode */
		if ($square) {
			
			/* thumb dimensions */
			if (!$upscaling && ($imageWidth < $maxWidth || $imageHeight < $maxHeight)) {
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
			if (!$upscaling && $imageWidth < $maxWidth && $imageHeight < $maxHeight) {
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
		
		// /* check if resizing is needless */
		// if ($thumbWidth == $info[0] && $thumbHeight == $info[1]) {
			// return $this->useOriginalAsThumb[$mode] = true;
		// }
		
		/* load image from file */
		switch ($info[2]) {
			case IMAGETYPE_GIF: $image = imagecreatefromgif($this->location); break;
			case IMAGETYPE_JPEG: $image = imagecreatefromjpeg($this->location); break;
			case IMAGETYPE_PNG: $image = imagecreatefrompng($this->location); break;
			case IMAGETYPE_WBMP: $image = imagecreatefromwbmp($this->location); break;
			default: return false;
		}
		
		/* create dir */
		$dir = dirname($saveTo);
		if (!is_dir($dir)) {
			mkdir($dir, 0777, true);
		}
		
		/* resizing */
		
		// /* simply copy */
		// if ($thumbWidth == $info[0] && $thumbHeight == $info[1]) {
			// return copy($this->location, $saveTo);
		// }
		
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
			case IMAGETYPE_JPEG: return imagejpeg($thumb, $saveTo, $this->lonely->JPEGQuality);
			case IMAGETYPE_PNG: return imagepng($thumb, $saveTo, $this->lonely->PNGConpression);
			case IMAGETYPE_WBMP: return imagewbmp($thumb, $saveTo);
		}
	}
}

class LonelyGenericFile extends LonelyFile {
	
	protected $thumbLocationPattern;
	protected $genericFileName = 'file.png';
	
	function __construct(LonelyGallery $lonely, $filename, LonelyAlbum $parentAlbum) {
		parent::__construct($lonely, $filename, $parentAlbum);
		
		if ($this->filename !== "") {
			$this->thumbLocationPattern = $this->lonely->thumbDir.'generic'.DIRECTORY_SEPARATOR.'<mode>'.DIRECTORY_SEPARATOR.$this->genericFileName;
		}
	}
	
	/* returns the HTML code for the preview */
	public function getPreviewHTML() {
		$path = $this->lonely->escape($this->getThumbPath('700px'));
		$name = $this->lonely->escape($this->getName());
		return "<img src=\"".$path."\" alt=\"".$name."\">";
	}
	
	/* returns the web thumb path */
	public function getThumbPath($mode) {
		return $this->thumbAvailable($mode) ? $this->lonely->thumbPath.'generic/'.$mode.'/'.$this->genericFileName : $this->lonely->thumbScript.$mode.'/'.$this->path;
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
		return file_put_contents($saveTo, base64_decode($this->base64EncodedThumbFile));
	}
	
	protected $base64EncodedThumbFile = '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';
}


/* class to extend when developing a module */
abstract class LonelyModule {
	
	/* contains the reference to the LonelyGallery instance */
	protected $lonely = null;
	
	/* sets the LonelyGallery reference */
	function __construct(LonelyGallery $lonely) {
		$this->lonely = $lonely;
		$this->afterConstruct();
	}
	
	/* executed after __construct() */
	public function afterConstruct() {
		/* nothing */
	}
	
	/* return settings for lonely */
	public function settings() {
		return array();
	}
	
	/* handle request */
	public function handleRequest(LonelyRequest $request) {
		/* return true if further requests handling is allowed */
		return true;
	}
}

/* class to extend when developing a design */
abstract class LonelyDesign extends LonelyModule {
	
	/* returns an array with css files to be loaded as design */
	public function getCSSFiles() {
		return array();
	}
}

/* default design */
class DefaultLonelyDesign extends LonelyDesign {
	
	/* returns an array with css files to be loaded as design */
	public function getCSSFiles() {
		return array($this->lonely->configScript.'lonely.css');
	}
	
	/* config files */
	public function configAction(LonelyRequest $request) {
		if ($request->action[0] == 'lonely.css') {
			$this->displayLonelyCSS();
		} else {
			$this->lonely->error();
		}
	}
	
	/* lonely.css */
	public function displayLonelyCSS() {
		
		$lastmodified = filemtime(__FILE__);
		if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE']) && $lastmodified && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $lastmodified) {
			header("HTTP/1.1 304 Not Modified", true, 304);
			exit;
		}
		
		header("Last-Modified: ".date(DATE_RFC1123, $lastmodified));
		header('Content-Type: text/css');
		?>
body {
    margin: 20px auto;
	background-color: #222;
	color: #fff;
	font-family: Arial,Helvetica,sans-serif;
	font-size: 14px;
	max-width: 1240px;
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
	padding: 0 20px;
	margin: 10px auto 20px;
}
#albums a, #images a {
	color: #fff;
}
#albums li, #images li {
	position: relative;
	display: block;
	float: left;
	width: 150px;
	height: 150px;
	overflow: hidden;
	background-color: #111;
	text-align: center;
	line-height: 130px;
}
#images li {
	width: 300px;
	height: 300px;
	line-height: 280px;
}
#images li img {
	height: 300px;
	width: 300px;
}
#albums li a, #images li a {
	position: absolute;
	top: 0;
	left: 0;
	width: 130px;
	height: 130px;
	padding: 10px;
	background-color: rgba(0,0,0,0);
	transition: background-color 0.3s;
	-moz-transition: background-color 0.3s;
	-webkit-transition: background-color 0.3s;
	-o-transition: background-color 0.3s;
}
#images li a {
	width: 280px;
	height: 280px;
	background-color: rgba(0,0,0,.4);
	opacity: 0;
	transition: opacity 0.3s;
	-moz-transition: opacity 0.3s;
	-webkit-transition: opacity 0.3s;
	-o-transition: opacity 0.3s;
}
#albums li a:hover, #albums li a:focus {
	background-color: rgba(0,0,0,.4);
}
#images li a:hover, #images li a:focus {
	opacity: 1;
}
#albums li a span, #images li a span {
	background-color: #111;
	display: inline-block;
	line-height: 150%;
	padding: 4px 8px;
	box-shadow: 0 0 2px #111;
	vertical-align: middle;
}
.imagenav, .image {
	text-align: center;
}
.imagenav p {
	margin: 0;
}
.imagenav p a {
	display: inline-block;
	line-height: 400%;
}
.imagenav p *:nth-child(1):before { content: "<< "; }
.imagenav p *:nth-child(2):before { content: "< "; }
.imagenav p *:nth-child(3):after { content: " >"; }
.imagenav p *:nth-child(4):after { content: " >>"; }
.image {
	margin: 0 auto;
}
.image img {
	display: block;
	max-width: 100%;
	margin: 0 auto;
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
	width: 30%;
	min-width: 50px;
	color: #fff;
	background-color: rgba(0,0,0,.4);
	opacity: 0;
	transition: opacity 0.3s;
	-moz-transition: opacity 0.3s;
	-webkit-transition: opacity 0.3s;
	-o-transition: opacity 0.3s;
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
}
?>