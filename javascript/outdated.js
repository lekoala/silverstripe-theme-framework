function addLoadEvent(func) {
	var oldonload = window.onload;
	if (typeof window.onload != 'function') {
		window.onload = func;
	} else {
		window.onload = function () {
			if (oldonload) {
				oldonload();
			}
			func();
		}
	}
}
addLoadEvent(function () {
	outdatedBrowser({
		bgColor: '$BgColor',
		color: '$Color',
		lowerThan: '$LowerThan',
		languagePath: '/theme-framework/javascript/outdatedbrowser/lang/$Lang.html'
	});
});