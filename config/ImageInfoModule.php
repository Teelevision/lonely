<?php
/*
##########################
### Image Info Module  ###
###        for         ###
### Lonely PHP Gallery ###
##########################

### Version ###

1.1.0 beta 1
date: 2013-08-29

### Requirements ###

Lonely PHP Gallery 1.1.0 beta 1 or above
Exif library for PHP

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

The Image Info Module displays additional meta infos that are stored within a
JPEG image file on the detail page of the image.
Also it replaces the image name with the title from the meta data.

### Installation ###

Place the PHP file into the 'config' directory.

### Settings ###

* imageInfo_technical
Show additional infos like camera name, exposure time, ISO, ... . Just create a
file called 'imageInfo_technical' without any extension in your 'config'
directory. If you want to disable it in a album, place a empty file called
'-imageInfo_technical' in the albums 'config' directory.
* imageInfo_notitle
Disable replacement of the default image name. Use 'imageInfo_notitle' without
any extension.

*/

namespace LonelyGallery\ImageInfoModule;
use \LonelyGallery\Lonely as Lonely;
class ImageInfoModule extends \LonelyGallery\Module {
	
	private $tmp_names = array();
	
	
	/* executed after __construct() */
	public function afterConstruct() {
		/* check for EXIF */
		if (!function_exists('exif_read_data')) {
			Lonely::model()->error(500, 'Missing EXIF library. Make sure your PHP installation includes the EXIF library.');
		}
	}
	
	/* returns the replacing title of the file or null on none replacement */
	public function elementNamesEvent(\LonelyGallery\Element $element) {
		if (empty(Lonely::model()->imageInfo_notitle) && $element instanceof \LonelyGallery\File && $element->getMime() == 'image/jpeg') {
			$location = $element->getLocation();
			if ($location !== '') {
				/* cached value */
				if (isset($this->tmp_names[$location]) && $this->tmp_names[$location] !== "") {
					return $this->tmp_names[$location];
				}
				return $this->tmp_names[$location] = $this->getJpegName($location);
			}
		}
		return null;
	}
	
	protected function getJpegName($location) {
		
		/* IPTC */
		if (getimagesize($location, $info)
			&& isset($info['APP13'])
			&& ($iptc = iptcparse($info['APP13']))
			&& isset($iptc['2#105'][0])
			&& ($name = trim($iptc['2#105'][0])) !== '')
		{
			return Lonely::model()->utf8ify($name);
		}
		
		/* EXIF */
		if ($exif = exif_read_data($location)) {
			if (isset($exif['ImageDescription']) && ($name = trim($exif['ImageDescription'])) !== '') {
				return Lonely::model()->utf8ify($name);
			} else if (isset($exif['Title']) && ($name = trim($exif['Title'])) !== '') {
				return Lonely::model()->utf8ify($name);
			}
		}
		
		return null;
	}
	
	/* returns an array of information about the image */
	public function fileInfoEvent(\LonelyGallery\File $file) {
		switch ($mime = $file->getMime()) {
			case 'image/jpeg':
				return $this->getMetadata($file->getLocation(), $mime);
				break;
			case 'image/png':
				return $this->getFiledata($file->getLocation(), $mime);
				break;
		}
		return array();
	}
	
	/* returns the EXIF data */
	protected function getFiledata($location, $mime) {
		
		$metadata = array();
		
		$gis = getimagesize($location);
		
		/* resolution */
		if (!empty($gis[0]) && !empty($gis[1])) {
			$metadata['Resolution'] = $gis[0].' x '.$gis[1].' pixel';
		}
		
		/* filesize */
		$metadata['Filesize'] = round(filesize($location) * 0.001, 2).' kB';
		
		return $metadata;
	}
	
	/* checks a value */
	public static function isEmpty($value) {
		return !(is_scalar($value) ? trim($value) : $value);
	}
	
	/* returns the EXIF data */
	protected function getMetadata($location, $mime) {
		
		$metadata = array();
		
		/* load data */
		/* EXIF */
		if (!($exif = exif_read_data($location))) {
			$exif = array();
		}
		/* IPTC */
		if (($gis = getimagesize($location, $info)) && isset($info['APP13'])) {
			if (!($iptc = iptcparse($info['APP13']))) {
				$iptc = array();
			}
		}
		
		$lonely = Lonely::model();
		
		// /* title */
		// if (isset($iptc['2#105'][0]) && !self::isEmpty($iptc['2#105'][0])) { // headline
			// $metadata[] = $lonely->utf8ify($iptc['2#105']);
		// } else if (isset($exif['Title']) && !self::isEmpty($exif['Title'])) {
			// $metadata[] = $lonely->utf8ify($exif['Title']);
		// }
		/* description */
		if (isset($iptc['2#120'][0]) && !self::isEmpty($iptc['2#120'][0])) { // caption
			$metadata[] = $lonely->utf8ify($iptc['2#120'][0]);
		} else if (isset($exif['ImageDescription']) && !self::isEmpty($exif['ImageDescription'])) {
			$metadata[] = $lonely->utf8ify($exif['ImageDescription']);
		} else if (isset($exif['Subject']) && !self::isEmpty($exif['Subject'])) {
			$metadata[] = $lonely->utf8ify($exif['Subject']);
		}
		/* photographer */
		if (isset($iptc['2#080'][0]) && !self::isEmpty($iptc['2#080'][0])) { // by-line
			$metadata['Photographer'] = $lonely->utf8ify($iptc['2#080'][0]).(empty($iptc['2#085'][0]) ? '' : ' ('.$lonely->utf8ify($exif['2#085'][0]).')');
		} else if (isset($exif['Artist']) && !self::isEmpty($exif['Artist'])) {
			$metadata['Photographer'] = $lonely->utf8ify($exif['Artist']);
		} else if (isset($exif['Author']) && !self::isEmpty($exif['Author'])) {
			$metadata['Photographer'] = $lonely->utf8ify($exif['Author']);
		}
		/* copyright */
		if (isset($iptc['2#116'][0]) && !self::isEmpty($iptc['2#116'][0])) {
			$metadata['Copyright'] = $lonely->utf8ify($iptc['2#116'][0]);
		} else if (isset($exif['Copyright']) && !self::isEmpty($exif['Copyright'])) {
			$metadata['Copyright'] = $lonely->utf8ify($exif['Copyright']);
		}
		/* time */
		if (isset($exif['DateTimeOriginal']) && !self::isEmpty($exif['DateTimeOriginal'])) {
			$metadata['Date and time'] = date('j M Y, H:i', strtotime($exif['DateTimeOriginal']));
		} else if (isset($iptc['2#055'][0]) && !self::isEmpty($iptc['2#055'][0])) {
			if (isset($iptc['2#060'][0]) && !self::isEmpty($iptc['2#060'][0])) {
				$metadata['Date and time'] = date('j M Y, H:i', strtotime($exif['2#055'][0].(empty($iptc['2#060'][0]) ? '' : $exif['2#060'][0])));
			} else {
				$metadata['Date'] = date('j M Y', strtotime($exif['2#055'][0]));
			}
		}
		/* location */
		$location = array();
		foreach (array('2#090', '2#095', '2#101') as $l) {
			if (isset($iptc[$l][0]) && !self::isEmpty($iptc[$l][0])) {
				$location[] = $lonely->utf8ify($iptc[$l][0]);
			}
		}
		if (count($location)) {
			$metadata['Location'] = implode(', ', $location);
		}
		/* keywords */
		if (isset($iptc['2#025'][0])) {
			$keywords = array();
			foreach ($iptc['2#025'] as $keyword) {
				$keyword = trim($lonely->utf8ify($keyword));
				if ($keyword !== '') {
					$keywords[] = $keyword;
				}
			}
			if (count($keywords)) {
				$metadata['Keywords'] = implode(', ', $keywords);
			}
		}
		/* resolution */
		if (!empty($exif['COMPUTED']['Width']) && !empty($exif['COMPUTED']['Height'])) {
			$metadata['Resolution'] = $exif['COMPUTED']['Width'].' x '.$exif['COMPUTED']['Height'].' pixel';
		} else if (!empty($gis[0]) && !empty($gis[1])) {
			$metadata['Resolution'] = $gis[0].' x '.$gis[1].' pixel';
		}
		/* filesize */
		if (isset($exif['FileSize']) && !self::isEmpty($exif['FileSize'])) {
			$metadata['Filesize'] = round($exif['FileSize'] * 0.001, 2).' kB';
		} else {
			$metadata['Filesize'] = round(filesize($location) * 0.001, 2).' kB';
		}
		
		if (!empty($lonely->imageInfo_technical)) {
			/* camera model */
			if (isset($exif['Model']) && !self::isEmpty($exif['Model'])) {
				$metadata['Camera'] = $exif['Model'];
				// if (isset($exif['Make']) && !self::isEmpty($exif['Make'])) {
					// $metadata['Camera'] .= ' ('.$exif['Make'].')';
				// }
			}
			/* exposure time */
			if (isset($exif['ExposureTime']) && !self::isEmpty($exif['ExposureTime'])) {
				$metadata['Exposure time'] = $exif['ExposureTime'].' s';
			}
			/* aperture */
			if (!empty($exif['COMPUTED']['ApertureFNumber'])) {
				$metadata['Aperture'] = $exif['COMPUTED']['ApertureFNumber'];
			}
			/* ISO */
			if (isset($exif['ISOSpeedRatings']) && !self::isEmpty($exif['ISOSpeedRatings'])) {
				$metadata['ISO'] = $exif['ISOSpeedRatings'];
			}
			/* flash */
			if (isset($exif['Flash'])) {
				if ($exif['Flash'] & 1) {
					$metadata['Flash'] = 'yes';
					// if ($exif['Flash'] & 24) $metadata['Flash'] .= ", auto";
					// if ($exif['Flash'] & 64) $metadata['Flash'] .= ", red-eye reduction";
				} else {
					$metadata['Flash'] = 'no';
				}
			}
		}
		
		return $metadata;
	}
}
?>