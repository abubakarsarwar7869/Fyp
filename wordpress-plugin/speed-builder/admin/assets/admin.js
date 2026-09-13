(function () {
	'use strict';
	/* Keep native admin interactions lightweight; page creation and template insertion are regular nonce-protected WordPress forms. */
	document.addEventListener('click', function (event) {
		var link = event.target.closest('a[href="#speed-builder-create-page"]');
		if (link) {
			event.preventDefault();
			document.getElementById('speed-builder-create-page').scrollIntoView({ behavior: 'smooth', block: 'center' });
			document.getElementById('speed-builder-page-title').focus();
		}
	});
}());
