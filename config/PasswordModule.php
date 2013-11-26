<?php
/*
##########################
###  Password Module   ###
###        for         ###
### Lonely PHP Gallery ###
##########################

### Version ###

1.1.0 beta 1
date: 2013-11-26

### Requirements ###

Lonely PHP Gallery 1.1.0 beta 1 or above
PHP sessions

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

The Password Module protects the gallery with a password. The password is set
via a 'password.txt' file in the config directory.

### Security notice ###

Please note that this cannot protect the original files from someone who knows
the url or is able to guess it. You could deny any access to the original
files through your web server configuration. If doing so, don't forget to set
'hideDownload' accordingly.
This module only serves the purpose to hide your images from most people.
Someone with special knowledge might be able to see your images after all.

### Installation ###

Place the PHP file into the 'config' directory. Create a file named
'password.txt' in the same directory containing the plain text password.

*/

namespace LonelyGallery\PasswordModule;
use \LonelyGallery\Lonely as Lonely;
class PasswordModule extends \LonelyGallery\Module {
	
	/* interrupt access */
	public function checkAccess(\LonelyGallery\Request $request) {
		/* check if password is required */
		$password = trim(Lonely::model()->password);
		if ($password !== '' && !(count($request->scope) && $request->scope[0] == 'config')) {
			/* make session available */
			session_start();
			/* login */
			if (isset($_POST['password'])) {
				if ($_POST['password'] == $password) {
					session_regenerate_id(true);
					$_SESSION['passwords'][] = $password;
				} else {
					Lonely::model()->error(403, 'The password is wrong!');
				}
			}
			/* display login page if session isn't logged in */
			else if (empty($_SESSION['passwords']) || !in_array($password, $_SESSION['passwords'])) {
				$this->displayLoginPage();
			}
			session_write_close();
		}
		return true;
	}
	
	/* interrupt access */
	private function displayLoginPage() {
		$html = "<form action=\"".$_SERVER['REQUEST_URI']."\" method=\"post\">\n".
			"\t<p>Password:\n".
			"\t\t<input type=\"password\" name=\"password\">\n".
			"\t\t<input type=\"submit\" value=\"OK\">\n".
			"\t</p>\n".
			"</form>\n";
		Lonely::model()->HTMLContent = $html;
		Lonely::model()->display();
		exit;
	}
}
?>