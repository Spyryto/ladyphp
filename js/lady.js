var Lady = {};
Lady.rules = {"keywords":"abstract|and|as|break|callable|case|catch|class|clone|const|continue|declare|default|do|echo|else(if)?|end(declare|for(each)?|if|switch|while)?|extends|false|final|for(each)?|function|global|goto|if|implements|include(_once)?|instanceof|insteadof|interface|namespace|new|null|or|parent|print|private|protected|public|require(_once)?|return|self|static|switch|throw|trait|true|try|use|var|while|xor|yield|array|binary|bool(ean)?|double|float|int(eger)?|object|real|string|unset","methodPrefix":"\\b(?:private|protected|public)(?:\\s+ static)?\\s+","classId":"(^|[^>$]|[^-]>) \\b(?:self|static|parent|[A-Z]\\w*|_+[A-Z])\\b","varId":"\\b (?:[a-z]\\w*|_+[a-z]\\w*|GLOBALS|_SERVER|_REQUEST|_POST|_GET|_FILES|_ENV|_COOKIE|_SESSION) \\b","toPhp":{"\\$":"\\\\$","(^|[^\\\\]) @@":"$1self::","(^|[^\\\\]) @":"$1$this->","\\.([^.=0-9])":"->$1","\\.(\\.|->)":".","({classId}) ->":"$1::","(^|[^>$\\\\]) ({varId} (?!\\s*\\() )":"$1$$2","(^|[^\\\\]) \\$ ({keywords}) \\b":"$1$2","<\\?\\$php \\b":"<?php","(^|[^\\?:\\s\\\\]) : (\\s)":"$1 =>$2","(\\b (case|default) \\b [^\\v]*) \\s =>":"$1:","<\\? (?!php\\b|=)":"<?php","({methodPrefix}) ({varId} \\s*\\( )":"$1function $2","\\\\@":"@","\\\\ \\$":"$","\\\\:":":"},"toLady":{"@":"\\@","(->) \\$":"$1\\\\$","\\$\\$":"\\\\$\\\\$","\\$ ({keywords}) \\b":"\\\\$$1","(?m) (^|[^\\s\\?]) : (\\s)":"$1\\:$2","\\$this->":"@","\\b self::":"@@","\\. (?![=0-9])":"..","->":".","({classId}) ::":"$1.","(^|[^\\\\]) \\$ ({varId} \\b (?!\\s*\\() )":"$1$2","(^|[^\\s]) \\s? => (\\s)":"$1:$2","<\\?php \\b":"<?","({methodPrefix}) function \\s+ ({varId} \\s*\\()":"$1$2","\\\\ \\$":"$"},"tokens":"^(?: (?: \\?> (?:[^<]|<[^?])* (<\\?(?:php\\b)?)? )|(?: \"[^\"\\\\]*(?:\\\\[\\s\\S][^\"\\\\]*)*\" | '[^'\\\\]*(?:\\\\[\\s\\S][^'\\\\]*)*' )|(?: (?:\/\/|\\#)[^\\n]*\| \/\\* (?:[^*]|\\*(?!\/))* \\*\/) )"};

Lady.toPhp = function(input) {
  return Lady.convert(input, Lady.rules.toPhp);
};

Lady.toLady = function(input) {
  return Lady.convert(input, Lady.rules.toLady);
};

Lady.toRegExp = function(str) {
  var flags = str.match(/^\(\?m\)/) ? 'mg' : 'g';
  str = str.replace(/\s+/g, '').replace(/^\(\?m\)/, '');
  return new RegExp(str, flags);
};

Lady.convert = function (input, rules) {
  var output = '', code = '';
  input = '?>' + input;
  var tokensPattern = Lady.toRegExp(Lady.rules.tokens);
  while (input || code) {
    var matches = tokensPattern.exec(input);
    if (matches || !input) {
      var token = (matches && matches[0]) ? matches[0] : '';
      var phpTag = (matches && matches[1]) ? matches[1] : '';
      token = phpTag ? token.substr(0, token.length - phpTag.length) : token;
      output += (code ? Lady.convertCode(code, rules) : '') + token;
      input = input.substr(token.length);
      code = '';
    } else {
      code += input[0];
      input = input.substr(1);
    }
  }
  return output.substr(2);
};

Lady.convertCode = function(input, rules) {
  var expand = function(s, id) { return Lady.rules[id]; };
  var replaceParts = function(x, a, b) {
    return replacement.replace(/\$1/, a).replace(/\$2/, b);
  };
  for (var i in rules) {
    var pattern = Lady.toRegExp(i.replace(/{(\w+)}/g, expand));
    var replacement = rules[i].replace(/\\\$/, '$');
    input = input.replace(pattern, replaceParts);
  }
  return input;
};

if (typeof module !== 'undefined') {
  module.exports = Lady;
}
