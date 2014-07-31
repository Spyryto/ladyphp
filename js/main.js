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
  titleBox.style.top = (top * 0.2) + 'px';
};
