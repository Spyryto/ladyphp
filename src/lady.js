var Lady = {};
Lady.rules = require('./rules.json');

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
  var tokensPattern = Lady.toRegExp(Lady.rules.tokens.join(''));
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

module.exports = Lady;
