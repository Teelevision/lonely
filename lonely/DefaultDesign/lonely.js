function adjustMaxImageHeight() {
	var img = document.querySelectorAll(".file img.preview");
	for (var i = 0; i < img.length; ++i) {
		adjustImageHeight(img[i]);
	}
}
function adjustImageHeight(image) {
	image.style.maxHeight = window.innerHeight + 'px';
}
function navigate(event) {
	var k = event.keyCode;
	switch (k) {
		case 37: // left arrow
		case 39: // right arrow
			var a = document.querySelector(".file .nav a[rel='next']");
			if (a) {
				window.location = a.href;
				return false;
			}
			break;
	}
}

window.addEventListener('resize', adjustMaxImageHeight);
window.addEventListener('keydown', navigate);
