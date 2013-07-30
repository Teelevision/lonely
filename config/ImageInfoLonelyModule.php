<?php
/*
##########################
### Image Info Module  ###
###        for         ###
### Lonely PHP Gallery ###
##########################

### Version ###

1.1.0 beta 1
date: 2013-07-29

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

class ImageInfoLonelyModule extends LonelyModule {
	
	/* executed after __construct() */
	public function afterConstruct() {
		
		/* check for EXIF */
		if (!function_exists('exif_read_data')) {
			$this->error(500, 'Error 500: Missing EXIF library. Make sure your PHP installation includes the EXIF library.');
		}
		
		// var_dump (!empty($this->lonely->imageInfo_title));
		if (empty($this->lonely->imageInfo_notitle)) {
			$this->lonely->extensionMap = array_merge(
				$this->lonely->extensionMap,
				array(
					'jpg' => 'ImageInfoLonelyImageFile',
					'jpeg' => 'ImageInfoLonelyImageFile',
				)
			);
		}
	}
	
	/* returns an array of information about the image */
	public function fileInfoEvent(LonelyFile $file) {
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
	
	/* returns the EXIF data */
	protected function getMetadata($location, $mime) {
		
		$metadata = array();
		
		if ($exif = exif_read_data($location)) {
			// var_dump('######### EXIF #########', $exif);
		}
		
		if ($gis = getimagesize($location, $info)) {
			if (isset($info['APP13'])) {
				$iptc = iptcparse($info['APP13']);
				// var_dump('######### IPTC #########', $iptc);
			}
		}
		
		if (empty($exif)) $exif = array();
		if (empty($iptc)) $iptc = array();
		
		// /* title */
		// if (!empty($iptc['2#105'])) { // headline
			// $metadata[] = $iptc['2#105'];
		// } else if (!empty($exif['Title'])) {
			// $metadata[] = $exif['Title'];
		// }
		/* description */
		if (!empty($iptc['2#120'][0])) { // caption
			$metadata[] = $iptc['2#120'][0];
		} else if (!empty($exif['ImageDescription'])) {
			$metadata[] = $exif['ImageDescription'];
		} else if (!empty($exif['Subject'])) {
			$metadata[] = $exif['Subject'];
		}
		/* photographer */
		if (!empty($iptc['2#080'][0])) { // by-line
			$metadata['Photographer'] = $iptc['2#080'][0].(empty($iptc['2#085'][0]) ? '' : ' ('.$exif['2#085'][0].')');
		} else if (!empty($exif['Artist'])) {
			$metadata['Photographer'] = $exif['Artist'];
		} else if (!empty($exif['Author'])) {
			$metadata['Photographer'] = $exif['Author'];
		}
		/* copyright */
		if (!empty($iptc['2#116'][0])) {
			$metadata['Copyright'] = $iptc['2#116'][0];
		} else if (!empty($exif['Copyright'])) {
			$metadata['Copyright'] = $exif['Copyright'];
		}
		/* time */
		if (!empty($exif['DateTimeOriginal'])) {
			$metadata['Date and time'] = date('j M Y, H:i', strtotime($exif['DateTimeOriginal']));
		} else if (!empty($iptc['2#055'][0])) {
			if (!empty($iptc['2#060'][0])) {
				$metadata['Date and time'] = date('j M Y, H:i', strtotime($exif['2#055'][0].(empty($iptc['2#060'][0]) ? '' : $exif['2#060'][0])));
			} else {
				$metadata['Date'] = date('j M Y', strtotime($exif['2#055'][0]));
			}
		}
		/* location */
		$location = array();
		foreach (array('2#090', '2#095', '2#101') as $l) {
			if (!empty($iptc[$l][0])) {
				$location[] = $iptc[$l][0];
			}
		}
		if (count($location)) {
			$metadata['Location'] = implode(', ', $location);
		}
		/* keywords */
		if (isset($iptc['2#025'][0])) {
			$keywords = array();
			foreach ($iptc['2#025'] as $keyword) {
				$keyword = trim($keyword);
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
		if (!empty($exif['FileSize'])) {
			$metadata['Filesize'] = round($exif['FileSize'] * 0.001, 2).' kB';
		} else {
			$metadata['Filesize'] = round(filesize($location) * 0.001, 2).' kB';
		}
		
		if (!empty($this->lonely->imageInfo_technical)) {
			/* camera model */
			if (!empty($exif['Model'])) {
				$metadata['Camera'] = $exif['Model'];
				// if (!empty($exif['Make'])) {
					// $metadata['Camera'] .= ' ('.$exif['Make'].')';
				// }
			}
			/* exposure time */
			if (!empty($exif['ExposureTime'])) {
				$metadata['Exposure time'] = $exif['ExposureTime'].' s';
			}
			/* aperture */
			if (!empty($exif['COMPUTED']['ApertureFNumber'])) {
				$metadata['Aperture'] = $exif['COMPUTED']['ApertureFNumber'];
			}
			/* ISO */
			if (!empty($exif['ISOSpeedRatings'])) {
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

class ImageInfoLonelyImageFile extends LonelyImageFile {
	
	private $tmp_name = "";
	
	/* return the name of this element */
	public function getName() {
		if ($this->tmp_name !== "") {
			return $this->tmp_name;
		}
		if (getimagesize($this->location, $info) && isset($info['APP13']) && ($iptc = iptcparse($info['APP13'])) && isset($iptc['2#105'][0]) && $iptc['2#105'][0] !== '') {
			return $this->tmp_name = $iptc['2#105'][0];
		}
		if ($exif = exif_read_data($this->location)) {
			if (isset($exif['ImageDescription']) && $exif['ImageDescription'] !== '') {
				return $this->tmp_name = $exif['ImageDescription'];
			} else if (isset($exif['Title']) && $exif['Title'] !== '') {
				return $this->tmp_name = $exif['Title'];
			}
		}
		return $this->tmp_name = parent::getName();
	}
}
?>