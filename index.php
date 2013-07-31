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
		// return str_replace($this->lonely->rootDir, $this->lonely->thumbDir.$mode.DIRECTORY_SEPARATOR, $this->location);
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
		return new LonelyFile(self::$lonely, $filename, $parentAlbum);
	}
}

class LonelyFile extends LonelyElement {
	
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
}
.image-box {
	display: inline-block;
	position: relative;
	margin: 0 0 10px;
	max-width: 100%;
}
.image-box a {
	display: block;
	position: absolute;
	top: 0;
	left: 0;
	height: 100%;
	width: 25%;
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
	position: relative;
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