<?php
namespace LonelyGallery;

/* class auto-loading */
spl_autoload_register(function ($class) {
	
	/* we are using namespaces, so there must be an backslash */
	if ($p = strrpos($class, '\\')) {
		
		/* split it up into namespace and classname */
		$namespace = substr($class, 0, $p);
		$classname = substr($class, $p + 1);
		
		/* the directory is defined by it's namespace */
		switch ($namespace) {
			case 'LonelyGallery':
				$file = path(array(__DIR__, $classname.'.php'));
				break;
			case 'LonelyGallery\\DefaultDesign':
				$file = path(array(__DIR__, 'DefaultDesign', $classname.'.php'));
				break;
			default:
				return;
		}
		
		/* check if the file is there */
		if (is_file($file)) {
			include $file;
		}
		
	}
});
?>