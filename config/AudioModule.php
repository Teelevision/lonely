<?php
/*
##########################
###    Audio Module    ###
###        for         ###
### Lonely PHP Gallery ###
##########################

### Version ###

1.1.0 beta 1
date: 2014-01-12

### Requirements ###

Lonely PHP Gallery 1.1.0 beta 1 or above

### License ###

Copyright (c) 2014 Marius 'Teelevision' Neugebauer

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

Embedds audio on the preview page that HTML5 browsers may play (ogg/mp3/wav).

### Installation ###

Place the PHP file into the 'config' directory.

*/

namespace LonelyGallery\AudioModule;
use \LonelyGallery\Lonely,
	\LonelyGallery\GenericFile;
class Module extends \LonelyGallery\Module {
	
	/* returns array of file classes to priority */
	public function fileClasses() {
		return array(
			'AudioFile' => 5,
		);
	}
}
class AudioFile extends GenericFile {
	protected $genericFileName = 'audio.png';
	protected $base64EncodedThumbFile = 'iVBORw0KGgoAAAANSUhEUgAAASwAAAEsCAYAAAB5fY51AAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAAsTAAALEwEAmpwYAAAQWklEQVR42u3dy44c53kG4K6qnuFJFEmRVKiTJVkK7ThOYgQOgqxyB9nkEux1LiH3EGRlwPcQBFl4GzgJkiDOGaYlhYosmSeJ4vkw5Ex3VRZefW8Z1ZiQlqY0zwNoUaru6uqq4jvVH77/r2b43jvDAmAGWocAEFgAAgsQWAACC0BgAQILQGABCCxAYAEILACBBQgsAIEFILAAgQUgsAAEFiCwAAQWgMACBBaAwAIQWIDAAhBYAAILEFgAAgsQWAACC0BgAQILQGABCCxAYAEILACBBQgsgINkeeD38IeXnSX4onz/XXdYAAILEFgAB9Fydnt8wH9jw6zMrEbsDgsQWAACCxBYAAILQGABAgtAYAEILEBgAQgsAIEFCCwAgQUgsACBBSCwAAQWILAABBaAwAIEFoDAAhBYgMACEFgAAgsQWAACCxBYDgEgsAAEFiCwAAQWgMACBBaAwAIQWIDAAhBYAAILEFgAAgtAYAECC0BgAQgs4Ctk6RAwZ8OiKcvNYnBQ3GEBCCwAgQV89ahhcaBljSoXhyb+5vbrqZfjDgtAYAEILGCeDn0NK7t21Dy+XH3WpHK5rctDG5fware+vV85qO6wAAQWgMAC5k8Nq+nKcjOsp18fy2pez1e/PFKP99aR+BMbl2xXz9/Q9/UCf3Snnq+oaTl/7rAABBYgsABm4dDXsPqokTS7j8vyqMa1qDWSxVCrWl/1Gtfz/n7Zd7V+4aW6/vipuGK36ufH2MH18dN1/dX3ynIXNa3F0Mf3GZ7x+EzPz2X+LndYgMACEFgA/y+Hvoa1evF8TfBbV8pyv32srl89rRuIvp4h/gZkX9dBr3FtrMHss29tvP04fNF3tX7l3bp89vX6hq2j9fOz5vjiuXp++716/j76z8nz96xjD4eu/pNq1nv7Wo87LEBgAQgsgF/p0NWwhqbWaFavfqMekOjT2TvzWl1//7Oy3D15WJb7rFE8fVR3ID5/iD6u/da09tvXk9+/yT6ytos31D6lrMEsVvusYcVYwPXJs/UFr12sf1Hf+t26frvWsBa7T+r3Of5iPR97tea4vvlxff2Tx3G+Hkwfnw1naH30hfo/du7X9Ufq+mbn7vT5HPWJucMCEFgAAgs4lGZXw9o8Vmsx+Zt/ffRkWe6//p2yvLpzrS6/9Xt1A7+4VJfvXK/bjxpLc+vp5P4smqhh9et9ff98Lt/G+bxyPqn4vP7I8bq91V4cv1qDaR98PtrDqf1bHas1pvVrtYbYvl1rVs3r34wrdmty/5ut7bo6xn72l39S3/+o1pDa2/V8tTFH/NBO/41fnX2jvj6upz77/tax/ahZdavp9Rv/fQzD5PmZW03MHRYgsAAEFnBoza+GFX1Aw6jmk7/K62/+1ctv1xrBW79TltdRM2gv1NcPUSNZX681ndH8TjvR1xM1oVHf1NPoC8oaTLcdf3JiTvMN83llDa/JGkn0Ma37un99zFfV7dybPvqxv3uvf6u+/pt/VL/OG7Vm1Z0+H6e3yyti8vw3r8bYxNd+s+7P/ZjzPfq6lo9ux+br+c351PocC3n6Qn3/C2cmz98i+voWj+P47u7E15/uoxvVROP1nTssAIEFCCyAeZhdDWs0VqvPOdbrcpt9Rt/+47K8deHNuv5I9FGdqHOED9FnNbz0Sl2+W8ca7kbNanmvrh/1wcT6Rczn1EcfUz6nL+dXWh05Ud9/6uW6v8taY2qjRreKsW/Dsbq8zjnSo8aYNbPhG39Yj8fF79bl03X/Fvlcwma6c6jJsZJNrRn1b0df3b1ao1pln9NnH8UFWOfLyjnnm6jBDdHX1h6rx2Md18/iSp2Dfsg+sbs36vqsYY3m24o+tZjPTQ0LQGABAgtgJmZXw1q9VOf4Xp+oNYom5h/qYqxW+63a99O9UGtU3dFac8iazLAdY9WyT+jGz8vi3ladE3599f26v9FH1Mec5aOxeVnDy/2LPpy987VG1+Z8Uee/VpdjrFy/HWMLcz6x7POJGs1wotZ4uot/UJfP5Zzt8ZzI7tmqLE2MPWzf/O36+Q9rn1N/7ERcX/FcxOiD6s+8Wv9BvV37+rqsceVYx7OvxufV63Ed86kNV+r102+czyz61KLGecQdFoDAAgQWwDzMrw8rxp5lX1ETY/famB9qK8YGtlEzGaLm0cTYsZxPqok+m3xuXo7N62Ms2Wh+o+jDyj6ppqv7l2Mb+9sx/9K5WqNql3X/uxhr10QNq835s6IPaYgaXht9WjkHexd9b23UjLJm1TTPOGNTfJ/uXJ2jf4iaUR/nazhZx04OOdY0noPYnXtj8vvlWMQ2amRZcxtWq8nrJ+XRGs1BPwzusAAEFoDAAuZodjWs5rdqDWsZNYfsk2mzJpOvj/VNZng+N3Bre/L93Uvx/qhxjZ7Dl8/9e1T7yBbxeU3Of5V9N1nDi+/btbWm0cZYtqxhjUoeWcM68/Lk8cn9HdVs2udcsxpdMDG2MGpsy5zP6mitOQ0noqaVc8hv1z677OtrlvlPLObkz5rp+dfj82I+tOwDzO3F65uN88e5wwIQWIDAApiF2dWwuvO1zyVrJkPMP9VtbU3+pt9YM9lYU4lDGDWNNvpoRjWnHCsYNZPsu9pkOFn7dNojdX9yju9uQ01vNGd41LAWx2tNqMm+rTh8o+PffsF/M/M5jjkfWJ6fvL6yhhXnJ/uoFhu+3+j6y+dC5hv2TscGokYVNdHsI5zdgwjdYQECC0BgAfzS/PqwRn0+8RWW0XeTNYXn3OczqsH0sf2o+Yz2J/ticg7zfM7epppP1ryyz6mdrmlkjSn7xMaNWcP09kY1lC+3iDLqM8u+pexzG9WEmsnvO+r72m+NdNhQ85p+DOP4eMf5afK5lu6wAAQWILAA5mF2NazhcR0rN5rTPGoCfc7pHvMb7bumkn1JuRw1qeFhfe7dkM/pe7qz4fNirGH0DWWNbGQ75vvKmlPOwZ7by+8Xz7VbNPurqTVZA/uCa1pZsxqNrcv1G45HGo/dW0x/39Hxjc+Ll/fxnMIcS5jPqcwdaLLvzh0WgMACBBbAPMzvuYSf/Kz+jyMnJn/zNzH/03DmQhyBDX1aWbPYMLaujxpbf+3Duv8n6xzgi4d3cofr9p8+ruuzBhFjJ5usWcT8T33WxLLGlH1gWZO7fyuOfx2rmGM5c3vdqZgDPmpsv/axhfFcvmH36eT+rx/cntzcqCZ3NuYXizntsy9rNN/Vg3p8h6b2hfXX43p68qRu//jJye+Tc9ovzr3iDgtAYAECC2AOZlfDWvzX39bf6NlXlTWaqFn073ynJnb0IW3sC4qazjrmYF/fiucCfvzTuv54zFd18+P6+pwD/dG9uj5qQIu9WoNp8/vGc+za6EsbDU2LOcmzT6y58VEc/zOTr29zbOLF79btvRhz3Occ589Y0xrXiGrNcPXwXux/rRn21y7v6/OaqMm18dzD7IPL/Vv94v26Pp+Defnf6vLNK3V/z0RNKs7H3lvfLsvHLv6+OywAgQUILIA5mF0Na/uDf6y/+c99rSZw1HS6nVqjWH36p3V9PAcu5/ge9SHt1b6X1bX/rS+PPpnm6gd1OWoSy9tX6/bX9fPa3VpTWR+NOdRzjvGsYR2vNZTu6cOyvBt9Pot4TuEQNZ+t6/X79KejZvK4Hu9F7P/qRNTUYn6zfG7hyL7HftYa0Tr74m5dr6+PmmHz4b/WzeVQyKg5rqPG2J8+H+cr9if6pIb//nFd7ur2lx/+e/33ENfP+va1OP5Rw/JcQgCBBSCwgHmaXQ1rGfMB5Vi4dlX7jLqd2ie1G30sOf9Rk2PjomY17DyqNYNL/1Dff++z+vk3ao2rjRrCcrdub7FX97+Jvp3uUY49jDnEsyYSY9Oa6MPqf1b3f33+zfp5d65Pf/6dG3X7Ow8nj/+TC++W5VU813B5oV6S7bETG66IDTWtqGn27/1zXY6+ue5pPR/Lq+/VT9ut10N/tO7f+pNL9fh/fmry+lxHn9Ty/X+qnxd9bN39m3V7cXyz5pqfN1y55A4LQGABCCxgjmZXw2qH+hu9i76fHEvX9vFcwMu1r2aVfUxRk8i+nEXUaJY//bvYw6g5PYwaUu5P1tCG7JNpJrc/xPxZzWjO8lUcv7p++/M6lnEv+naWD27G/tXPb3dijv2sIUYNsL38k7K8jrGIixg7uPyNNzccj1gbNZ/1vc/rCz6sNcxl1BzbOJ7d/fr+Jr5f+6ReD+sce5g1pNH7a81s6/aVOB4xljDm1M+a6CJqcDk/2lbUJN1hAQgsQGABzMRy7l8g53fKGs7oC1//n7K8G3OyDzFWr3tQaxg5f9FW1IBGfWHRB9QM0/uXfVc5f9KogpNzuOfrNzxHr40a01b2+cT63IGc0z5rcLk/W5/WvrTVB/9Sltf9hjnnc8777DuKvq3+k9pHtbxRa0xNzH81LLOv78nk8Rxy/a1ag1rerX1q2Sc1qomNjnfcU2x6LmI/fT20ezvusAAEFoDAAuZo9jWs0XxQG16fNamtGLs3qmHFfFqjOdSjLyafezeuMe3z+21cPzzb8cvl+D6j7efHDat97e/ySe3bGq7U50yuY6zoXs7/lc+RjLF97an63Mfm0t/X9TH2ron5x/L8baoBjvrS7n5ar5/s4xodz+nrY9jn9bPpemg31FDdYQEILEBgARxQ869h7ff10eeyzD6sGIuVc6qP5s/KD5h7jWCf+7/v4x/Hexk1xe5xrWGt3r8QG4i/sfkcwfNvlOXtT+pzIdsNNaph/Wznb/nwdnze7oYjNjzX4yuwYOaO/Plf/doCOHUOt5+EAAIL8JPwIBuNNexjLGKOjev312fEpp9cMdYx5y+Lms/2z/8jrtj6HMMm5pNaRw2py7F8CCyYle+/6xj8yZ/98j8/CYFZhtVf/4XAAmYSVn/zl34SHjg59ivnnxo2zD/Fc9VsGCuX83P1UcPK+aNybGf23QmrXxFWP/rBLMLqcAYWCKvZ3Vn5SQjurGYVVu6wwJ2VwDrImo1jtwYX+Rd6Pjb8BBjN2b/huY7xnMBm5mM7hZWfhCCsZkpggbASWICwet4U3TnQRnOqDxvm8D/MYz+/4mHlDguElcAChJXAAg5tWC0WaljMTPOM64WVOyxAWAksQFgJLBBWAgsQVgILhNWhDyuBBcJKYAHCSmCBsDq0YSWwQFgJLEBYCSwQVgILEFYCCxBWAguElcAChJXAAoSVwAJhJbAAYSWwQFgJK4EFwkpgAcJKYIGwElYCC4SVwAKElcACYYXAAmElsABhJbBAWCGwQFgJLEBYCSwQVggsEFYCC4SVsBJYIKwQWCCsBBYIKwQWCCsEFggrgQXCCoEFwkpgAcJKYIGwQmCBsBJYIKyElcACYYXAAmElsEBYCSuBBcIKgQXCSmCBsEJgwZcZWMJqlpaz2+MfXnbWeL5+9ANhJbBgBtxZ+UkI7qxwhwXurA61ZvjeO4PDAPhJCCCwAIEFILAABBYgsAAEFoDAAgQWgMACEFiAwAIQWAACCxBYAAILQGABAgtAYAEILEBgAQgsAIEFCCwAgQUgsACBBSCwAIEFILAABBYgsAAEFoDAAgQWgMACEFiAwAIQWAD79H8vtOoiWBnh3QAAAABJRU5ErkJggg==';

	/* file pattern */
	public static function pattern() {
		return '/\.(ogg|mp3|wav)$/i';
	}
	
	/* returns the HTML code for the preview */
	public function getPreviewHTML() {
		$path = Lonely::model()->rootPath.$this->path;
		return "<audio class=\"preview preview-controls-sideways\" controls src=\"".Lonely::escape($path)."\"></audio>";
	}
}
?>