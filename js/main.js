var ladyBox = document.getElementById('lady');
var phpBox = document.getElementById('php');

var processing = false;
function convert(toLady) {
  if (!processing) {
    processing = true;
    if (toLady) {
      ladyBox.value = Lady.toLady(phpBox.value);
    } else {
      phpBox.value = Lady.toPhp(ladyBox.value);
    }
    processing = false;
  }
}
convert(false);

ladyBox.onkeyup = function(){ convert(false); };
phpBox.onkeyup = function(){ convert(true); };

var skip = false;
ladyBox.onscroll = function() {
  if (skip){skip=false; return;} else skip=true; 
  phpBox.scrollTop = ladyBox.scrollTop;
}
phpBox.onscroll = function() {
  ladyBox.scrollTop = phpBox.scrollTop;
}

