// code editors
var editors = []
var textareas = [
	document.getElementById('lady'),
	document.getElementById('php'),
]
for (i in textareas) {
	editor = CodeMirror.fromTextArea(textareas[i], {
		value: textareas[i].value,
		mode: "php",
		theme: "solarized light",
		smartIndent: false,
		tabSize: 2,
		indentWithTabs: true,
		matchBrackets: true,
		autoCloseBrackets: true,
	});
	var convert = function(ed) {
		if (ed.hasFocus()) {
			if (ed == editors[0]) {
				editors[1].setValue(Lady.toPhp(ed.getValue()));
			} else {
				editors[0].setValue(Lady.toLady(ed.getValue()));
			}
			pos = ed.getScrollInfo();
			other = (ed == editors[0]) ? 1 : 0;
			editors[other].scrollTo(pos.left, pos.top)
		}
	};
	editor.on('change', convert);
	editor.on('focus', convert);
	editor.on('scroll', function(ed) {
		if (ed.hasFocus()) {
			pos = ed.getScrollInfo();
			other = (ed == editors[0]) ? 1 : 0;
			editors[other].scrollTo(pos.left, pos.top)
		}
	})
	editors[i] = editor;
}
editors[0].focus();
editors[0].setCursor(100);

// background scroll effect
document.onscroll = function() {
	var top = (window.pageYOffset || document.documentElement.scrollTop);
	document.getElementById('top-stripe').style.top
		= (window.innerWidth > 550) ? (top * 0.25) + 'px' : '0';
};

// analytics
(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','//www.google-analytics.com/analytics.js','ga');
ga('create', 'UA-30581306-2', 'auto');
ga('send', 'pageview');
