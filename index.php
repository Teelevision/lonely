<?php
/*
##########################
### Lonely PHP Gallery ###
##########################

### Version ###

1.1.0 beta 1
date: 2013-08-29

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

namespace LonelyGallery;

error_reporting(E_ALL ^ E_NOTICE ^ E_STRICT);

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
	
	/* whether to use the original file rather than a rendered version on a preview page */
	// 'useOriginals' => false,
	
	/* whether to hide the download link on a preview page */
	// 'hideDownload' => false,
	
	/* the number of images in an album thumbnail is the square of this value, e.g. "2" will result in 2x2=4 images */
	// 'albumThumbSquare' => 2,
	
);

/* aaand ... action! */
Lonely::model()->run($settings);


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
	
	/* file extensions of the files that should appear in the gallery */
	public $extensions = array('jpg', 'jpeg', 'png', 'gif');
	
	/* map file extension on class */
	public $extensionMap = array(
		'jpg' => 'ImageFile',
		'jpeg' => 'ImageFile',
		'png' => 'ImageFile',
		'gif' => 'ImageFile',
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
	
	/* file containing the name of the thumb file of a album */
	public $albumThumbNameFile = '_thumb.txt';
	
	/* album class to use */
	public $albumClass = 'Album';
	
	/* class name of the default design which is used if there is no design module */
	public $defaultDesign = 'DefaultDesign';
	
	/* default file action */
	public $defaultFileAction = 'preview';
	
	/* whether to use the original file rather than a rendered version on a preview page */
	public $useOriginals = false;
	
	/* whether to hide the download link on a preview page */
	public $hideDownload = false;
	
	/* the number of images in an album thumbnail is the square of this value, e.g. "2" will result in 2x2=4 images */
	public $albumThumbSquare = 2;
	
	/* css files to be loaded */
	public $cssfiles = array();
	
	/* javascript files to be loaded */
	public $jsfiles = array();
	
	/* hidden names */
	public $hiddenNames = array();
	public $hiddenFileNames = array();
	public $hiddenAlbumNames = array();
	public $hiddenNamesPattern = array();
	public $hiddenFileNamesPattern = array();
	public $hiddenAlbumNamesPattern = array();
	
	/* modules */
	private $modules = array();
	
	/* design */
	private $design = null;
	
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
		$this->configDir = $this->rootDir.$this->configDirectoryName.DIRECTORY_SEPARATOR;
		if (!is_dir($this->configDir)) {
			if (!mkdir($this->configDir)) {
				$this->error(500, 'Config directory (/'.$this->configDirectoryName.') could not be created. Check if your user has permission to write to your gallery directory.');
			}
		}
		
		/* read data from config dir */
		if (is_readable($this->configDir) && is_executable($this->configDir)) {
			$this->readConfig($this->configDir);
		} else {
			if (!is_readable($this->configDir)) {
				$this->error(500, 'Config directory (/'.$this->configDirectoryName.') is not readable.');
			} else {
				$this->error(500, 'Config directory (/'.$this->configDirectoryName.') cannot be entered due to missing executing rights.');
			}
		}
		
		/* render directory */
		$this->thumbDir = $this->rootDir.$this->thumbDirectoryName.DIRECTORY_SEPARATOR;
		if (!is_dir($this->thumbDir)) {
			if (!mkdir($this->thumbDir)) {
				$this->error(500, 'Thumbnail directory (/'.$this->thumbDirectoryName.') could not be created. Check if your user has permission to write to your gallery directory.');
			}
			if (!is_readable($this->thumbDir)) {
				$this->error(500, 'Thumbnail directory (/'.$this->thumbDirectoryName.') is not readable.');
			}
		}
		
		/* the gallery's full URL */
		$this->server = 'http'.(empty($_SERVER['HTTPS']) ? '' : 's').'://' // scheme
				.$_SERVER['SERVER_NAME'] // domain
				.((($_SERVER['SERVER_PORT'] == 80 && empty($_SERVER['HTTPS'])) || ($_SERVER['SERVER_PORT'] == 443 && !empty($_SERVER['HTTPS']))) ? '' : ':'.$_SERVER['SERVER_PORT']); // port
		/* root-relative path */
		$this->rootPath = dirname($_SERVER['SCRIPT_NAME']).'/';
		$this->rootScript = $_SERVER['SCRIPT_NAME'].'?/';
		$this->rootScriptClean = $_SERVER['SCRIPT_NAME'];
		$this->thumbPath = $this->rootPath.$this->thumbDirectoryName.'/';
		$this->thumbScript = $this->rootScript.$this->thumbDirectoryName.'/';
		$this->configPath = $this->rootPath.$this->configDirectoryName.'/';
		$this->configScript = $this->rootScript.$this->configDirectoryName.'/';
		
		/* init request */
		$scopes = array(
			array(Request::MATCH_STRING, $this->configDirectoryName),
			array(Request::MATCH_REGEX, $this->thumbDirectoryName.'/[0-9]+(px|sq)'),
		);
		$this->request = new Request($scopes);
		$album = $this->request->album;
		
		/* read data from album config dir */
		$num = count($album);
		for ($n = 1; $n <= $num; ++$n) {
			$dir = $this->rootDir.implode(DIRECTORY_SEPARATOR, array_slice($album, 0, $n)).DIRECTORY_SEPARATOR.$this->configDirectoryName.DIRECTORY_SEPARATOR;
			if (is_readable($dir) && is_executable($dir)) {
				$this->readConfig($dir);
			}
		}
		
		/* initialize modules */
		$this->initModules();
		
		/* check for hidden files and directories */
		$file = $this->request->file;
		if ($file && $this->isHiddenFileName($file)) {
			$this->request->moveFileToAction();
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
		
		/* let modules handle the request */
		foreach ($this->modules as $module) {
			if (method_exists($module, 'handleRequest')) {
				if (!call_user_func(array($module, 'handleRequest'), $request)) {
					return;
				}
			}
		}
		
		$scope = $request->scope;
		$action = $request->action;
		
		switch ($scope[0]) {
			
			/* thumb */
			case $this->thumbDirectoryName:
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
		
		/* bring the action */
		$somethingCalled = false;
		if (method_exists($this, $method)) {
			call_user_func(array($this, $method), $this->request);
			$somethingCalled = true;
		}
		foreach ($this->modules as $module) {
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
		if ($name === '' || $name[0] == '.' || $name[0] == '-' || in_array($name, $this->hiddenNames)) {
			return true;
		}
		foreach ($this->hiddenNamesPattern as $pattern) {
			if (preg_match($pattern, $name)) {
				return true;
			}
		}
		return false;
	}
	
	/* evaluates if the file name is hidden */
	public function isHiddenFileName($name) {
		if ($this->isHiddenName($name) || $name == $this->albumThumbName || $name == $this->albumThumbNameFile || in_array($name, $this->hiddenFileNames)) {
			return true;
		}
		foreach ($this->hiddenFileNamesPattern as $pattern) {
			if (preg_match($pattern, $name)) {
				return true;
			}
		}
		return false;
	}
	
	/* evaluates if the dir name is hidden */
	public function isHiddenAlbumName($name) {
		if ($this->isHiddenName($name) || $name == $this->configDirectoryName || $name == $this->thumbDirectoryName || in_array($name, $this->hiddenAlbumNames)) {
			return true;
		}
		foreach ($this->hiddenAlbumNamesPattern as $pattern) {
			if (preg_match($pattern, $name)) {
				return true;
			}
		}
		return false;
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
			if (!class_exists('\\'.__NAMESPACE__.'\\'.$name.'\\'.$name)) {
				require($this->configDir.$name.'.php');
			}
		}
		
		/* init objects */
		foreach ($this->modules as $name => &$module) {
			
			/* initialize module */
			$classname = '\\'.__NAMESPACE__.'\\'.$name.'\\'.$name;
			$module = new $classname();
			
			/* fetch settings from module */
			$this->set($module->settings());
			
			/* file types */
			$filetypes = $module->fileTypes();
			if (is_array($filetypes) && count($filetypes)) {
				foreach ($filetypes as &$filetype) {
					$filetype = $name.'\\'.$filetype;
				}
				$this->setExtensions(array_keys($filetypes));
				$this->addExtensionmap($filetypes);
			}
			
			/* switch designs */
			if ($module instanceof Design) {
				$this->design = $module;
			}
			
		}
		
		$this->_modulesInitialized = true;
		
	}
	
	/* calls an event */
	public function callEvent() {
		if (func_num_args() >= 1) {
			$args = func_get_args();
			$method = $args[0].'Event';
			$data = array();
			foreach ($this->modules as $moduleName => $module) {
				if (method_exists($module, $method)) {
					$data[$moduleName] = call_user_func_array(array($module, $method), array_slice($args, 1));
				}
			}
			return $data;
		}
	}
	
	/* add file extensions */
	public function setExtensions($value) {
		if (!is_array($value)) {
			preg_match_all('#[[:alnum:]]+#i', $value, $matches);
			$value = $matches[0];
		}
		foreach ($value as $v) {
			$this->extensions[] = strtolower($v);
		}
	}
	
	/* map file extensions to class */
	public function addExtensionmap(Array $map) {
		$this->extensionMap = array_merge($this->extensionMap, $map);
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
	protected function lonelyIndexAction(Request $request) {
		
		$classname = '\\'.__NAMESPACE__.'\\'.$this->albumClass;
		$album = new $classname($request->album);
		$file = FileFactory::create($request->file, $album);
		
		/* file requested */
		if ($file && $file->isAvailable()) {
			
			/* redirect to the file */
			header('Location: '.$this->server.$this->rootPath.$file->getPath(), true, 301);
			exit;
			
		}
		
		/* album requested */
		else if ($album->isAvailable()) {
			
			$html = $this->albumText ? '<div id="album-text">'.$this->albumText."</div>\n" : '';
			
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
			
			/* albums */
			$mode = '150sq';
			if (count($albums = $album->getAlbums())) {
				$html .= "<ul id=\"albums\">\n";
				foreach ($albums as $element) {
					$path = self::escape($this->rootScript.$element->getPath());
					$thumbpath = self::escape($element->getThumbPath($mode));
					$name = self::escape($element->getName());
					$html .= "\t<li id=\"".$element->getId()."\">\n".
						"\t\t<img src=\"".$thumbpath."\" alt=\"".$name."\">\n".
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
					$thumbpath = self::escape($element->getThumbPath($mode));
					$name = self::escape($element->getName());
					$html .= "\t<li id=\"".$element->getId()."\">\n".
						"\t\t<img src=\"".$thumbpath."\" alt=\"".$name."\">\n".
						"\t\t<a href=\"".$path."\"><span>".$name."</span></a>\n".
						"\t</li>\n";
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
			exit;
			
		}
		
		/* nothing requested */
		$this->error();
	}
	
	/* display thumb page */
	protected function lonelyPreviewAction(Request $request) {
		
		$classname = '\\'.__NAMESPACE__.'\\'.$this->albumClass;
		$album = new $classname($request->album);
		$file = FileFactory::create($request->file, $album);
		
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
				$html .= "<nav class=\"imagenav\">\n".
					"\t<p>\n".
					"\t\t".($first ? "<a href=\"".self::escape($this->rootScript.$first->getPath().'/'.$action)."\">first</a>" : "<span>first</span>")."\n".
					"\t\t".($prev ? "<a rel=\"prev\" href=\"".self::escape($this->rootScript.$prev->getPath().'/'.$action)."\">previous</a>" : "<span>previous</span>")."\n".
					"\t\t<a href=\"".self::escape($this->rootScript.$element->getPath())."#".$file->getId()."\">album</a>\n".
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
				$html .= "\t\t<a class=\"prev\" rel=\"prev\" href=\"".self::escape($this->rootScript.$prev->getPath().'/'.$action)."\"></a>\n";
			}
			if ($next) {
				$html .= "\t\t<a class=\"next\" rel=\"next\" href=\"".self::escape($this->rootScript.$next->getPath().'/'.$action)."\"></a>\n";
			}
			$html .= "\t</div>\n\n";
			
			/* info */
			$html .= "\t<div class=\"image-info\">\n".
				"\t\t<p class=\"title\">".$name."</p>\n";
			if (!$this->hideDownload) {
				$html .= "\t\t<p class=\"download\"><a href=\"".self::escape($this->rootPath.$file->getPath())."\">Download</a></p>\n";
			}
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
			
			$html .= "</div>\n\n";
			
			$this->HTMLContent = $html;
			$this->display();
			exit;
			
		}
		
		$this->error();
	}
	
	/* shows the thumbnail */
	protected function displayThumb(Request $request, $mode) {
		
		$classname = '\\'.__NAMESPACE__.'\\'.$this->albumClass;
		$element = $album = new $classname($request->album);
		/* file thumbnail */
		if ($request->file) {
			$element = FileFactory::create($request->file, $album);
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
	
	/* shows 150px square thumbnail */
	protected function thumb150sqAction(Request $request) {
		$this->displayThumb($request, '150sq');
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
				$this->modules = array();
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
	protected function display() {
		?><!DOCTYPE html>
<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<?php
		
		/* optional meta information */
		if (($m = $this->metaDescription) != "") {
			echo "\t<meta name=\"description\" content=\"", self::escape($m), "\">\n";
		}
		if (($m = $this->metaKeywords) != "") {
			echo "\t<meta name=\"keywords\" content=\"", self::escape($m), "\">\n";
		}
		if (($m = $this->metaAuthor) != "") {
			echo "\t<meta name=\"author\" content=\"", self::escape($m), "\">\n";
		}
		if (($m = $this->metaRobots) != "") {
			echo "\t<meta name=\"robots\" content=\"", self::escape($m), "\">\n";
		}
		
		/* CSS */
		$cssfiles = array_merge($this->design->getCSSFiles(), $this->cssfiles);
		foreach ($cssfiles as $file) {
			echo "\t<link type=\"text/css\" rel=\"stylesheet\" media=\"screen\" href=\"", self::escape($file), "\">\n";
		}
		
		/* JavaScript */
		foreach ($this->jsfiles as $file) {
			echo "\t<script type=\"text/javascript\" src=\"", self::escape($file), "\">\n\n";
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


abstract class Element extends Component {
	
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
	
	
	function __construct(Album $parent = null) {
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
}

class Album extends Element {
	
	/* array containing the album path */
	protected $album;
	
	/* albums and files in this album */
	private $_albums;
	private $_files;
	
	/* file to use as thumbnail */
	private $_thumbImage;
	
	
	function __construct(Array $album, self $parent = null) {
		$this->album = $album;
		parent::__construct($parent);
		
		$this->initId('album_'.end($this->album));
		$this->location = Lonely::model()->rootDir.(count($this->album) ? implode(DIRECTORY_SEPARATOR, $this->album).DIRECTORY_SEPARATOR : '');
		$this->thumbLocationPattern = Lonely::model()->thumbDir.'<mode>'.DIRECTORY_SEPARATOR.(count($this->album) ? implode(DIRECTORY_SEPARATOR, $this->album).DIRECTORY_SEPARATOR : '');
		$this->path = count($this->album) ? implode('/', array_map('rawurlencode', $this->album)).'/' : '';
	}
	
	/* loads the name of this element */
	protected function loadName() {
		if (($altname = $this->getAlternativeName()) !== null) {
			return $altname;
		}
		$name = count($this->album) ? end($this->album) : Lonely::model()->title;
		$name = strtr($name, '_', ' ');
		return $name;
	}
	
	/* returns the object of the parent album */
	public function getParent() {
		$parent = parent::getParent();
		if (!$parent && count($this->album)) {
			$this->setParent($parent = new self(array_slice($this->album, 0, -1)));
		}
		return $parent;
	}
	
	/* reads in the files and dirs within the directory of this album */
	public function loadElements() {
		
		$this->_albums = array();
		$this->_files = array();
		
		/* this is clean if this is a subdirectory which is not the config or thumb directory */
		$cleanLocation = count($this->album) && !in_array(Lonely::model()->configDirectoryName, $this->album) && $this->album[0] !== Lonely::model()->thumbDirectoryName;
		
		/* go through each element */
		$dir = opendir($this->location);
		while (($filename = readdir($dir)) !== false) {
			
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
						$classname = '\\'.__NAMESPACE__.'\\'.Lonely::model()->albumClass;
						$album = new $classname(array_merge($this->album, array($filename)), $this);
						$this->_albums[$filename] = $album;
					}
					break;
				
				case 'file':
					if (!Lonely::model()->isHiddenFileName($filename)) {
						$file = FileFactory::create($filename, $this);
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
	
	/* returns the thumb image object or false if an own should be rendered */
	public function getThumbImage() {
		if ($this->_thumbImage === null) {
			
			/* by name file */
			$nameFile = $this->location.Lonely::model()->albumThumbNameFile;
			if (is_file($nameFile) && (($name = @file_get_contents($nameFile)) !== false)) {
				$name = trim($name);
				$path = explode('/', $name);
				if ($c = count($path)) {
					
					/* root path */
					if ($c >= 2 && $path[0] === '') {
						$path = array_slice($path, 1);
					}
					/* relative path */
					else {
						$path = array_merge($this->album, $path);
					}
					
					/* consolidate path (remove '.' and '..')*/
					$num = count($path);
					for ($a = $b = 0; $a < $num; ++$a) {
						$b = $a - 1;
						while ($b >= 0 && !isset($path[$b])) {
							$b--;
						}
						/* remove '.' and empty parts */
						if ($path[$a] == '.' || $path[$a] === '') {
							unset($path[$a]);
						}
						/* implode with previous part */
						else if ($path[$a] == '..') {
							unset($path[$a]);
							if ($b >= 0 && $b < $a) {
								unset($path[$b]);
							}
						}
					}
					
					/* load objects */
					$album = array_slice($path, 0, -1);
					$album = $this->album == $album ? $this : new Album($album);
					$file = FileFactory::create(end($path), $album);
					
					/* check file */
					if ($file && $file->isAvailable()) {
						$this->_thumbImage = $file;
					}
				}
			}
			
			/* default name */
			if ($this->_thumbImage === null) {
				$file = new ImageFile(Lonely::model()->albumThumbName, $this);
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
		return parent::getThumbLocation($mode).rawurlencode(Lonely::model()->albumThumbName);
	}
	
	/* returns the web thumb path */
	public function getThumbPath($mode) {
		if ($thumbImage = $this->getThumbImage()) {
			return $thumbImage->getThumbPath($mode);
		}
		return parent::getThumbPath($mode).rawurlencode(Lonely::model()->albumThumbName);
	}
	
	/* checks if there is a up-to-date thumbnail file */
	public function thumbAvailable($mode) {
		if ($thumbImage = $this->getThumbImage()) {
			return $thumbImage->thumbAvailable($mode);
		}
		return parent::thumbAvailable($mode);
	}
	
	/* creates a thumbnail */
	protected function createThumb($mode, $saveTo) {
		
		/* mode for files. should be a mode that is used somewhere else to prevent rendering needless thumbnails */
		switch ($mode) {
			case '150sq':
			case '300sq':
			default: $fileMode = '300sq';
		}
		
		$num = max(1, Lonely::model()->albumThumbSquare);
		$num2 = $num * $num;
		
		/* get images */
		$files = array();
		$count = 0;
		foreach($this->getFiles() as $file) {
			if ($file->initThumb($fileMode)) {
				$files[] = $file->getThumbLocation($fileMode);
				++$count;
			}
			if ($count >= $num2) {
				break;
			}
		}
		/* not enough? add albums */
		if ($count < $num2) {
			foreach($this->getAlbums() as $album) {
				if ($album->initThumb($mode)) {
					array_unshift($files, $album->getThumbLocation($mode));
					++$count;
				}
				if ($count >= $num2) {
					break;
				}
			}
		}
		/* not enough? add duplicates */
		if ($count && $count < $num2) {
			for ($i = $num2 - 1, $a = 0; $i >= 0 && !isset($files[$i]); $i--) {
				if (!isset($files[$a])) {
					$a = 0;
				}
				$files[$i] = $files[$a++];
				++$count;
			}
		}
		
		/* create new image */
		switch ($mode) {
			case '150sq': $thumb = imagecreatetruecolor(150, 150); break;
			case '300sq': $thumb = imagecreatetruecolor(300, 300); break;
			default: return false;
		}
		
		/* modes */
		$square = false;
		$upscaling = false;
		switch ($mode) {
			case '150sq': $square = true; $maxWidth = $maxHeight = 150/$num; break;
			case '300sq': $square = true; $maxWidth = $maxHeight = 300/$num; break;
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
			$toX = ($nr % $num) * $maxWidth;
			$toY = floor($nr / $num) * $maxHeight;
			imagecopyresampled($thumb, $image, $toX, $toY, $imageX, $imageY, $thumbWidth, $thumbHeight, $imageWidth, $imageHeight);
			
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
		return imagejpeg($thumb, $saveTo, Lonely::model()->JPEGQuality);
	}
}

class FileFactory {
	
	/* returns the instance of the object or null if not supported */
	public static function create($filename, Album $parent) {
		$extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
		if (!in_array($extension, Lonely::model()->extensions)) {
			return null;
		}
		$extensionMap = Lonely::model()->extensionMap;
		if (isset($extensionMap[$extension])) {
			$classname = '\\'.__NAMESPACE__.'\\'.$extensionMap[$extension];
			return new $classname($filename, $parent);
		}
		return new GenericFile($filename, $parent);
	}
}

abstract class File extends Element {
	
	/* filename on the file system */
	private $_filename;
	
	
	function __construct($filename, Album $parent) {
		$this->_filename = $filename;
		parent::__construct($parent);
		
		$this->initId('file_'.$this->_filename);
		if ($this->_filename !== "") {
			$parent = $this->getParent();
			$this->location = $parent->getLocation().$this->_filename;
			$this->thumbLocationPattern = $parent->getThumbPathPattern().$this->_filename;
			$this->path = $parent->getPath().rawurlencode($this->_filename);
		}
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

class ImageFile extends File {
	
	private $_imageInfo;
	private $_useOriginalAsThumb = array();
	
	
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
			$this->_imageInfo = getimagesize($this->location);
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
		$path = empty(Lonely::model()->useOriginals) ? $this->getThumbPath('700px') : $this->getPath();
		$name = Lonely::escape($this->getName());
		return "<img src=\"".Lonely::escape($path)."\" alt=\"".$name."\">";
	}
	
	/* returns whether this file is suitable as a thumb without resizing */
	public function canUseOriginalAsThumb($mode) {
		if (!isset($this->_useOriginalAsThumb[$mode])) {
			/* evaluate whether this file has to be resized */
			
			/* get info */
			$info = $this->getImageInfo();
			
			switch ($mode) {
				case '150sq': $v = ($info[0] == $info[1] && $info[0] <= 150); break;
				case '300sq': $v = ($info[0] == $info[1] && $info[0] <= 300); break;
				case '700px': $v = ($info[0] <= 700 && $info[1] <= 700); break;
				default: $v = false;
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
		return $this->canUseOriginalAsThumb($mode) ? Lonely::model()->rootPath.$this->getPath() : parent::getThumbPath($mode);
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
			case IMAGETYPE_JPEG: return imagejpeg($thumb, $saveTo, Lonely::model()->JPEGQuality);
			case IMAGETYPE_PNG: return imagepng($thumb, $saveTo, Lonely::model()->PNGConpression);
			case IMAGETYPE_WBMP: return imagewbmp($thumb, $saveTo);
		}
	}
}

class GenericFile extends File {
	
	protected $thumbLocationPattern;
	protected $genericFileName = 'default.png';
	
	function __construct($filename, Album $parent) {
		parent::__construct($filename, $parent);
		
		if ($this->getFilename() !== "") {
			$this->thumbLocationPattern = Lonely::model()->thumbDir.'generic'.DIRECTORY_SEPARATOR.'<mode>'.DIRECTORY_SEPARATOR.$this->genericFileName;
		}
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
			
			case '150sq':
				$thumb = imagecreatetruecolor(150, 150);
				$image = imagecreatefromstring(base64_decode($this->base64EncodedThumbFile));
				imagecopyresampled($thumb, $image, 0, 0, 0, 0, 150, 150, 300, 300);
				imagedestroy($image);
				return imagepng($thumb, $saveTo, Lonely::model()->PNGConpression);
			
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
	
	/* returns array of file types like this: array('ext'=>'FileClassName', ...) */
	public function fileTypes() {
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
class DefaultDesign extends \LonelyGallery\Design {
	
	/* returns an array with css files to be loaded as design */
	public function getCSSFiles() {
		return array(Lonely::model()->configScript.'lonely.css');
	}
	
	/* config files */
	public function configAction(\LonelyGallery\Request $request) {
		if ($request->action[0] == 'lonely.css') {
			$this->displayCSS();
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
    margin: 20px auto;
	background-color: #222;
	color: #fff;
	font-family: Arial,Helvetica,sans-serif;
	font-size: 14px;
	width: 90%;
	width: calc(100% - 60px);
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
#albums li img {
	height: 150px;
	width: 150px;
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
.imagenav p * {
	display: inline-block;
	line-height: 400%;
}
.imagenav p *:nth-child(1):before { content: "<< "; }
.imagenav p *:nth-child(2):before { content: "< "; }
.imagenav p *:nth-child(3):before { content: "["; }
.imagenav p *:nth-child(3):after { content: "]"; }
.imagenav p *:nth-child(4):after { content: " >"; }
.imagenav p *:nth-child(5):after { content: " >>"; }
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