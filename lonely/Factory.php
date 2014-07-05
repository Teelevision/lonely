<?php
namespace LonelyGallery;

class Factory {
	
	/* elements */
	private static $_albums = array();
	private static $_files = array();
	
	
	/* returns the instance of the album */
	public static function createAlbum($gPath) {
		$path = implode('/', $gPath);
		
		/* check if object was already created */
		if (isset(self::$_albums[$path])) {
			return self::$_albums[$path];
		}
		
		/* create object */
		$parentStr = implode('/', array_slice($gPath, 0, -1));
		$parent = isset(self::$_albums[$parentStr]) ? self::$_albums[$parentStr] : null;
		$classname = Lonely::model()->albumClass;
		return self::$_albums[$path] = new $classname($gPath, $parent);
	}
	
	/* returns the instance of the album by path */
	public static function createAlbumByRelPath($path, Album $parent) {
		$gPath = explode('/', $path);
		return self::createAlbum(self::consolidateGalleryPath($gPath, $parent));
	}
	
	/* returns the instance of the file or null if not supported */
	public static function createFile($filename, Album $parent) {
		$gPath = array_merge($parent->getGalleryPath(), array($filename));
		$path = implode('/', $gPath);
		
		/* check if object was already created */
		if (isset(self::$_files[$path])) {
			return self::$_files[$path];
		}
		
		/* create object */
		$patterns = Lonely::model()->getFilePatterns();
		foreach ($patterns as $pattern => $classname) {
			if (preg_match($pattern, $filename)) {
				return self::$_files[$path] = new $classname($gPath, $filename, $parent);
			}
		}
		return null;
	}
	
	/* returns the instance of the object by path or null if not supported */
	public static function createFileByRelPath($path, Album $album) {
		$gPath = explode('/', $path);
		$gPath = self::consolidateGalleryPath($gPath, $album);
		/* load objects */
		$album = self::createAlbum(array_slice($gPath, 0, -1));
		return self::createFile(end($gPath), $album);
	}
	
	/* consolidates a path (given as array) */
	public static function consolidateGalleryPath(Array $gPath, Element $parent) {
		/* relative path */
		if (count($gPath) == 1 || $gPath[0] != '') {
			$gPath = array_merge($parent->getGalleryPath(), $gPath);
		}
		/* consolidate path (remove '', '.' and '..')*/
		$gPath = array_diff($gPath, array('', '.'));
		foreach ($gPath as $a => $v) {
			/* delete with previous part */
			if ($v == '..') {
				unset($gPath[$a]);
				for ($b = $a - 1; $b > 0 && !isset($gPath[$b]); ) --$b;
				unset($gPath[$b]);
			}
		}
		return $gPath;
	}
}
?>