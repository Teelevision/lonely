<?php
/*
##########################
### Lonely PHP Gallery ###
##########################
###    Lonely Core     ###
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

This is the Lonely Core class which is loaded and initialized by the
bootstrap. It handles settings, requests, modules, default pages,
html output, the interaction of all and more.
*/

namespace LonelyGallery;

if (!defined('LONELY_ONEFILE')) {
	require(__DIR__.DIRECTORY_SEPARATOR.'functions.php');
	require(__DIR__.DIRECTORY_SEPARATOR.'autoload.php');
	require(__DIR__.DIRECTORY_SEPARATOR.'Component.php');
}

/* core class */
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
	
	/* names of the thumb file of albums */
	public $albumThumb = array('_thumb.png', '_thumb.jpg');
	
	/* file containing the name of the thumb file of an album */
	public $albumThumbFile = '_thumb.txt';
	
	/* file containing text/html to display at top of an album */
	public $albumText = '_text.txt';
	
	/* file containing redirect path to different album */
	public $redirectFile = '_redirect.txt';
	
	/* auto redirect to only sub-album in otherwise empty album */
	public $autoRedirect = true;
	
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
	private $_design = '';
	
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
	
	public function run($rootDir, Array $settings = array()) {
		
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
		$this->rootDir = $rootDir.DIRECTORY_SEPARATOR;
		
		/* set default design */
		$this->_design = $this->defaultDesign;
		
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
		$this->hiddenFileNames[] = '/^('.implode('|', array_map('preg_quote', array_merge($this->albumThumb, array($this->albumThumbFile, $this->albumText, $this->redirectFile)))).')$/i';
		$this->hiddenAlbumNames[] = '/^('.preg_quote($this->configDirectory).'|'.preg_quote($this->thumbDirectory).'|lonely)$/i';
		
		/* initialize modules */
		$this->initModules();
		
		/* init render profiles */
		$renderProfiles = $this->_design->renderProfiles();
		RenderHelper::addProfiles($renderProfiles);
		
		/* init request */
		$scopePattern = '#^('.preg_quote($this->configDirectory).'|'.preg_quote($this->thumbDirectory).'/('.implode('|', array_map('preg_quote', array_keys($renderProfiles), array('#'))).'))(/|$)#';
		$this->request = new Request($this->rootDir, $scopePattern);
		$album = $this->request->album;
		
		/* init default files */
		$this->registerFileClass('\\LonelyGallery\\Image');
		if ($this->extensions) {
			$this->registerFileClass('\\LonelyGallery\\GenericFile');
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
			
			/* modules always end on 'Module.php' */
			else if (($s = substr($file, -10)) == 'Module.php') {
				$this->addModule(substr($file, 0, -4));
			}
			
			/* designs always end on 'Design.php' */
			else if ($s == 'Design.php') {
				/* replace previous design */
				$this->_design = substr($file, 0, -4);
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
			if (method_exists($module, 'checkAccess') && !$module->checkAccess($request)) {
				$this->error(403, 'You are not allowed to access this page.');
			}
		}
		
		/* let modules handle the request */
		foreach ($this->_modules as $module) {
			if (method_exists($module, 'handleRequest') && !$module->handleRequest($request)) {
				return;
			}
		}
		
		$scope = $request->scope;
		$action = $request->action[0] == '' ? array('index') : $request->action;
		
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
				if (in_array($request->file, $this->albumThumb)) {
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
		
		/* add design at front to modules */
		$this->_modules = array($this->_design => null) + $this->_modules;
		
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
			
			/* data */
			$parents = $album->getParents();
			$albums = $album->getAlbums();
			$files = $album->getFiles();
			
			/* redirect */
			/* placed here after loading the sub-albums and files because it takes the least time thanks to caching, also you probably want to redirect empty albums, so the overhead isn't that big */
			if (($redirectAlbum = $album->getRedirectAlbum()) || ($this->autoRedirect && !count($files) && count($albums) == 1 && ($redirectAlbum = reset($albums)))) {
				header('Location: '.$this->server.$this->rootScript.$redirectAlbum->getPath(), true, 302);
				exit;
			}
			
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
			if (count($albums)) {
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
			if (count($files)) {
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
		
		/* CSS & JS files */
		foreach ($this->getModules() as $module) {
			foreach ($module->resources() as $file => $res) {
				$path = Lonely::model()->configScript.$file;
				if ($res instanceof CSSFile) {
					echo "\t<link type=\"text/css\" rel=\"stylesheet\" href=\"", self::escape($path), "\"", ($res->media != '' ? " media=\"".self::escape($res->media)."\"" : ""), ">\n";
				} else if ($res instanceof JSFile) {
					echo "\t<script type=\"text/javascript\" src=\"", self::escape($path), "\"></script>\n";
				}
			}
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
	
<!-- execution: <?php echo round((microtime(true) - $this->startTime) * 1000, 3); ?> ms -->
</body>
</html><?php
		
	}
}
?>