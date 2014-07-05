<?php
namespace LonelyGallery;

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
		$thumb = self::createImage($this->s['width'], $this->s['width'], IMAGETYPE_PNG);
		
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
		return self::saveImage($thumb, $saveTo, IMAGETYPE_PNG);
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
?>