var ladyBox = document.getElementById('lady');
var phpBox = document.getElementById('php');

var skip = false;
ladyBox.onscroll = function() {
  if (skip){skip=false; return;} else skip=true;
  phpBox.scrollTop = ladyBox.scrollTop;
}
phpBox.onscroll = function() {
  ladyBox.scrollTop = phpBox.scrollTop;
}


var processing = false;
function convert(toLady) {
  if (!processing) {
    processing = true;
    if (toLady) {
      ladyBox.value = Lady.toLady(phpBox.value);
      phpBox.onscroll();
    } else {
      phpBox.value = Lady.toPhp(ladyBox.value);
      ladyBox.onscroll();
    }
    processing = false;
  }
}
convert(false);

ladyBox.onkeyup = function(){ convert(false); };
phpBox.onkeyup = function(){ convert(true); };
ladyBox.onchange = ladyBox.onkeyup;
phpBox.onchange = phpBox.onkeyup;

document.onscroll = function() {
  var titleBox = document.getElementById('title-box');
  var top = (window.pageYOffset || document.documentElement.scrollTop);
  titleBox.style.top = (window.innerWidth > 550) ? (top * 0.25) + 'px' : '0';
};

(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','//www.google-analytics.com/analytics.js','ga');
ga('create', 'UA-30581306-2', 'auto');
ga('send', 'pageview');
