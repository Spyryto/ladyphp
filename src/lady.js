var Lady = {};
Lady.rules = {"joiningKeywords":"and|as|extends|implements|instanceof|insteadof|x?or","leadingKeywords":"{joiningKeywords}|abstract|callable|case|catch|class|clone|const|declare|do|echo|else(?:if)?|final|for(?:each)?|function|global|goto|if|include(?:_once)?|interface|namespace|new|print|private|protected|public|require(?:_once)?|switch|throw|trait|try|use|var|while|yield|array|binary|bool(?:ean)?|double|float|int(?:eger)?|object|real|string|unset","keywords":"{leadingKeywords}|break|continue|default|end(?:declare|for(?:each)?|if|switch|while)?|false|null|parent|return|self|static|true","methodPrefix":"\\b(?:private|protected|public)(?:\\s+static)?\\s+","classId":"(^|[^>$]|[^-]>)\\b(?:self|static|parent|[A-Z]\\w*|_+[A-Z])\\b","varId":"\\b(?:[a-z]\\w*|_+[a-z]\\w*|GLOBALS|_SERVER|_REQUEST|_POST|_GET|_FILES|_ENV|_COOKIE|_SESSION)\\b","closure":"(^|[^.$])\\bfunction\\b[\\s']*\\(","statementEnd":"[\\s']*(\\n|$)(?![\\s']*([\\])\\.\\-\\+:=\/%*&|>,\\{?]|<[^?]|({joiningKeywords})\\b))","toPhp":{"\\$":"\\\\$","(^|[^\\\\])@@":"$1self::","(^|[^\\\\])@":"$1$this->","\\.([^.=0-9])":"->$1","(^|[^\\\\])~":"$1.","({classId})->":"$1::","(^|[^>$\\\\])({varId}(?!\\s*\\())":"$1$$2","(^|[^\\\\])\\$({keywords})\\b":"$1$2","([\\w\"\\]\\)\\-+]|[^\\{;\\s]\\})({statementEnd})":"$1;$2","((?:^|[^$>])\\b(?:{leadingKeywords}))\\;([\\s']*(\\n|$))":"$1$2","<\\?\\$php;?\\b":"<?php","(^|[^\\?:\\s\\\\]):(\\s)":"$1 =>$2","(\\b(case|default)\\b[^\\n]*)\\s\\=>":"$1:","<\\?(?!php\\b|=)":"<?php","({methodPrefix})({varId}\\s*\\()":"$1function $2","\\\\([~@$])":"$1"},"toLady":{"([@~])":"\\\\$1","(->)\\$":"$1\\\\$","\\$\\$":"\\\\$\\\\$","\\$({keywords})\\b":"\\\\$$1","\\$this->":"@","\\bself::":"@@","\\.(?![=0-9])":"~","->":".","({classId})::":"$1.","(^|[^\\\\])\\$({varId}\\b(?!\\s*\\())":"$1$2","(^|[^\\s])\\s? =>(\\s)":"$1:$2","<\\?php\\b":"<?","({methodPrefix})function\\s+({varId}\\s*\\()":"$1$2","\\\\\\$":"$",";([\\s']*\\n)":"$1"},"tokens":"(?:(?:(?:^|\\?>)(?:[^<]|<[^?])*(<\\?(?:php\\b)?)?)|(?:\"[^\"\\\\]*(?:\\\\[\\s\\S][^\"\\\\]*)*\"|'[^'\\\\]*(?:\\\\[\\s\\S][^'\\\\]*)*')|(?:(?:\/\/|\\#)[^\\n]*(?=\\n)|\/\\*(?:[^*]|\\*(?!\/))*\\*\/))"};

Lady.toPhp = function(input) {
  return Lady.convert(input, Lady.rules.toPhp);
};

Lady.toLady = function(input) {
  return Lady.convert(input, Lady.rules.toLady);
};

Lady.convert = function(code, rules) {
  var strings = [];
  var tokensPattern = new RegExp(Lady.rules.tokens, 'g');
  code = code.toString().replace(tokensPattern, function (string, phpTag) {
    strings.push(phpTag ? string.substr(0, string.length - phpTag.length) : string);
    return (string.match(/^['"]/) ? '""' : "''") + (phpTag ? phpTag : '');
  });
  closureBrackets = [];
  code = code.replace(/([^{}]*)([{}])/g, function (token, code, bracket) {
    if (bracket == '{') {
      closureBrackets.push(code.match(new RegExp(Lady.rules.closure)));
      return token;
    } else {
      return code + (closureBrackets.pop() ? '"}"' : '}');
    }
  });
  for (var i in rules) {
    var pattern = i
    while (pattern.match(/{(\w+)}/g)) {
      pattern = pattern.replace(/{(\w+)}/g, function(s, id) {
        return Lady.rules[id];
      });
    }
    var replacement = rules[i].replace(/\\\$/, '$');
    code = code.replace(new RegExp(pattern, 'g'), function(x, a, b) {
      return replacement.replace(/\$1/, a).replace(/\$2/, b);
    });
  }
  code = code.replace(/"}"/g, '}');
  return code.replace(/""|''/g, function () {
    return strings.shift();
  });
};

if (typeof module !== 'undefined') {
  module.exports = Lady;
}
