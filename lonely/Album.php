<?php
/*
##########################
### Lonely PHP Gallery ###
##########################
###       Album        ###
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

The Album class represents a folder.
*/

namespace LonelyGallery;

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
	
	/* album order */
	private $_order;
	
	
	function __construct(Array $gPath, self $parent = null) {
		parent::__construct($gPath, $parent);
		
		$gPath = $this->getGalleryPath();
		$this->initId('album_'.end($gPath));
		$this->location = Lonely::model()->rootDir.(count($gPath) ? path($gPath, true) : '');
		$this->thumbLocationPattern = Lonely::model()->thumbDir.'<profile>'.DIRECTORY_SEPARATOR.(count($gPath) ? path($gPath, true) : '');
		$this->path = count($gPath) ? webpath(array_map('rawurlencode', $gPath), true) : '';
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
		foreach (array('_albums', '_files') as $attr) {
			switch ($this->getOrder()) {
				case 'name':
				case 'asc':
				case 'name-asc':
					ksort($this->{$attr});
					break;
				case 'desc':
				case 'name-desc':
				case 'reversed':
					krsort($this->{$attr});
					break;
				case 'random':
					$tmpArray = array();
					foreach ($this->{$attr} as $k => $v)
						$tmpArray[] = array($k, $v);
					$this->{$attr} = array();
					shuffle($tmpArray);
					foreach ($tmpArray as $v)
						$this->{$attr}[$v[0]] = $v[1];
					break;
				case 'off':
				default: /* nothing */
			}
		}
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
	
	/* returns the array of visible and hidden files in this album */
	public function getAllFiles() {
		if ($this->_allFiles === null) {
			$this->loadElements();
		}
		return $this->_allFiles;
	}
	
	/* returns only those files of the given list that are in this album */
	public function getFilesNamed($files) {
		if ($this->_allFiles !== null) {
			return array_intersect((array)$files, $this->getAllFiles());
		}
		$result = array();
		foreach ((array)$files as $file) {
			if (is_file($this->location.$file)) {
				$result[] = $file;
			}
		}
		return $result;
	}
	
	/* returns the files that match the pattern */
	public function getFilesMatching($pattern) {
		return preg_grep($pattern, $this->getAllFiles());
	}
	
	/* returns the description text */
	public function getText() {
		if ($this->_text === null && $this->getFilesNamed(Lonely::model()->albumText)) {
			$text = file_get_contents($this->location.Lonely::model()->albumText);
			$this->_text = $text ?: '';
		}
		return $this->_text;
	}
	
	/* returns the album to redirect to or null */
	public function getRedirectAlbum() {
		if ($this->getFilesNamed(Lonely::model()->redirectFile)) {
			$path = file_get_contents($this->location.Lonely::model()->redirectFile);
			return Factory::createAlbumByRelPath($path, $this);
		}
		return null;
	}
	
	/* returns the order */
	public function getOrder() {
		if ($this->_order === null && $this->getFilesNamed(Lonely::model()->albumOrderFile)) {
			$order = file_get_contents($this->location.Lonely::model()->albumOrderFile);
			$this->_order = trim($order) ?: null;
		}
		return $this->_order ?: Lonely::model()->albumOrder;
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
				$this->_thumbImage = false;
				foreach (Lonely::model()->albumThumb as $name) {
					$file = Factory::createFile($name, $this);
					if ($file->isAvailable()) {
						$this->_thumbImage = $file;
						break;
					}
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
		return parent::getThumbLocation($profile).rawurlencode(Lonely::model()->albumThumb[0]);
	}
	
	/* returns the web thumb path */
	public function getThumbPath($profile) {
		if ($thumbImage = $this->getThumbImage()) {
			return $thumbImage->getThumbPath($profile);
		}
		return parent::getThumbPath($profile).rawurlencode(Lonely::model()->albumThumb[0]);
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
?>