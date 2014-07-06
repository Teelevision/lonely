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

The Generic File refers to files which is not rendered in a preview,
like text, audio and video. This class therefore carries a default
thumbnail to display instead.
*/

namespace LonelyGallery;

class GenericFile extends ContentFile {
	
	protected $thumbLocationPattern;
	protected $genericFileName = 'default.png';
	
	function __construct($gPath, $filename, Album $parent) {
		parent::__construct($gPath, $filename, $parent);
		
		if ($this->getFilename() !== "") {
			$this->thumbLocationPattern = path(array(Lonely::model()->thumbDir.'generic', '<profile>', $this->genericFileName));
		}
	}
	
	/* file pattern */
	public static function pattern() {
		return '/('.implode('|', Lonely::model()->extensions).')$/i';
	}
	
	/* returns the HTML code for the preview */
	public function getPreviewHTML() {
		$path = escape($this->getThumbPath(Lonely::model()->getDesign()->previewProfile($this)));
		$name = escape($this->getName());
		return "<img class=\"preview\" src=\"".$path."\" alt=\"".$name."\">";
	}
	
	/* returns the web thumb path */
	public function getThumbPath($profile) {
		return $this->thumbAvailable($profile) ? Lonely::model()->thumbPath.'generic/'.$profile.'/'.$this->genericFileName : Lonely::model()->thumbScript.$profile.'/'.$this->path;
	}
	
	/* checks if there is a up-to-date thumbnail file */
	public function thumbAvailable($profile) {
		$thumbPath = $this->getThumbLocation($profile);
		return ($thumbPath && is_file($thumbPath));
	}
	
	/* creates a thumbnail */
	protected function createThumb($profile, $saveTo) {
		$thumbPathOriginal = $this->getThumbLocation('original');
		/* create dir */
		touch_mkdir($thumbPathOriginal);
		return file_put_contents($thumbPathOriginal, base64_decode($this->base64EncodedThumbFile)) && RenderHelper::profile($profile)->renderThumbnailOfElement($this, $thumbPathOriginal);
	}
	
	protected $base64EncodedThumbFile = 'iVBORw0KGgoAAAANSUhEUgAAASwAAAEsCAYAAAB5fY51AAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAAsTAAALEwEAmpwYAAADzUlEQVR42u3YwQ2CQBRFUTS0oQspgxUNaQW4oANdUY0xUctgo/ShNZgYne8/pwIYkps3LPrr5lkBBLB0BIBgAQgWIFgAggUgWIBgAQgWgGABggUgWACCBQgWgGABCBYgWACCBSBYgGABCBaAYAGCBSBYAIIFCBaAYAEIFiBYAIIFCBaAYAEIFiBYAIIFIFiAYAEIFoBgAYIFUJK69Acc2slXgi/Z3xoLC0CwAMECKFEd7YFLv2NDJNH+EVtYgGABCBYgWACCBSBYgGABCBaAYAGCBSBYAIIFCBaAYAEIFiBYAIIFIFiAYAEIFoBgAYIFIFgAggUIFoBgAQgWIFgAggUIliMABAtAsADBAhAsAMECBAtAsAAECxAsAMECECxAsAAEC0CwAMECECwAwQIEC0CwAAQLECwAwQIQLECwAAQLQLAAwQIQLECwAAQLQLAAwQIQLADBAgQLQLAABAsQLADBAhAsQLAABAtAsADBAhAsAMECBAtAsAAECxAsAMECECxAsAAEC0CwAMECECxAsAAEC0CwAMECECwAwQIEC0CwAAQLECwAwQIQLECwAAQLQLAAwQIQLADBAgQLQLAABAsQLADBAhAsQLAABAtAsADBAhAsQLAABAtAsADBAhAsAMECBAtAsAAECxAsAMECECxAsAAEC0CwAMECECwAwQIEC0CwAAQLECwAwQIQLCCm2hHw74Z2cggWFoBgAQgWEJt/WKSzvzXpz6BbbatuvbOwgJixOt0PggXEiNX5cRQsoOxYXeYxRKyqyj8ssKyCxMrCAssq1LtYWGBZWViAWAkWkDJWggViJViAWAkWkDZWggViJViAWAkWkDZWggViJViAWAkWkDZWggViJViAWAkWiFXaWAkWiJVgAWIlWCBWaWMlWCBWggWIlWCBWAkWIFaCBYiVYIFYCRYgVoIFiJVggVgJFiBWggViJVaCBWIlWIBYCRaIlVgJFoiVYAFiJVggVggWiJVgAWIlWCBWCBaIlWABYiVYIFYIFoiVYIFYiZVggVghWCBWggVihWCBWCFYIFaCBWKFYIFYCRYgVoIFYoVggVgJFoiVWAkWiBWCBWIlWCBWYiVYIFYIFoiVYIFYIVjwq2CJVVh1tAce2slX46Mu8yhWggXls6xcCcGywsICyyq3RX/dPB0D4EoIIFiAYAEIFoBgAYIFIFgAggUIFoBgAQgWIFgAggUgWIBgAQgWgGABggUgWACCBQgWgGABCBYgWACCBSBYgGABCBYgWACCBSBYgGABCBaAYAGCBSBYAIIFCBaAYAG86QXYMa4//4/U4QAAAABJRU5ErkJggg==';
}
?>