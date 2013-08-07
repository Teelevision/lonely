<?php
/*
##########################
### Album Names Module ###
###        for         ###
### Lonely PHP Gallery ###
##########################

### Version ###

1.1.0 beta 1
date: 2013-08-02

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

class AlbumNamesLonelyModule extends LonelyModule {
	
	/* executed after __construct() */
	public function afterConstruct() {
		$this->lonely->hiddenFileNames = array_merge(
			$this->lonely->hiddenFileNames,
			array(AlbumNamesLonelyAlbum::$nameFile)
		);
	}
	
	/* settings for lonely */
	public function settings() {
		return array(
			'albumClass' => 'AlbumNamesLonelyAlbum',
		);
	}
}

class AlbumNamesLonelyAlbum extends LonelyAlbum {
	
	public static $nameFile = '_name.txt';
	private $tmp_name = '';
	
	/* return the name of this element */
	public function getName() {
		
		/* cached value */
		if ($this->tmp_name !== "") {
			return $this->tmp_name;
		}
		
		/* '_name.txt' file */
		$file = $this->location.self::$nameFile;
		if (is_file($file) && is_readable($file) && ($name = trim(file_get_contents($file))) !== '') {
			return $this->tmp_name = $this->lonely->utf8ify($name);
		}
		
		/* default way to get the name (dirname) */
		return $this->tmp_name = parent::getName();
	}
}
?>