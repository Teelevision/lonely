<?php
/*
##########################
### Album Names Module ###
###        for         ###
### Lonely PHP Gallery ###
##########################

### Version ###

1.1.0 beta 1
date: 2013-08-29

### Requirements ###

Lonely PHP Gallery 1.1.0 beta 1 or above

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

The Album Names Module let you define album names by adding a file named
'_name.txt' to the album directory containing your custom name.

### Installation ###

Place the PHP file into the 'config' directory.

*/

namespace LonelyGallery\AlbumNamesModule;
use \LonelyGallery\Lonely as Lonely;
class Module extends \LonelyGallery\Module {
	
	private $_nameFile = '_name.txt';
	private $_tmp_names = '';
	
	
	/* executed after __construct() */
	public function afterConstruct() {
		Lonely::model()->hiddenFileNames = array_merge(
			Lonely::model()->hiddenFileNames,
			array($this->_nameFile)
		);
	}
	
	/* returns the replacing title of the file or null on none replacement */
	public function elementNamesEvent(\LonelyGallery\Element $element) {
		if ($element instanceof \LonelyGallery\Album) {
			$location = $element->getLocation();
			if ($location !== '') {
				/* cached value */
				if (isset($this->_tmp_names[$location]) && $this->_tmp_names[$location] !== "") {
					return $this->_tmp_names[$location];
				}
				return $this->_tmp_names[$location] = $this->getAlbumName($location);
			}
		}
		return null;
	}
	
	protected function getAlbumName($location) {
		/* '_name.txt' file */
		$file = $location.$this->_nameFile;
		if (is_file($file) && is_readable($file) && ($name = trim(file_get_contents($file))) !== '') {
			return Lonely::model()->utf8ify($name);
		}
		
		return null;
	}
}
?>