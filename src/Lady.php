<?php

class Lady {
  public static $rules = [
    'parser' => '(?:(?:<\?php) | ((?:^|\?>) (?:[^<]|<(?:[^?]|$))* (?=<\?|$))
      |(?:"[^"\\\\]*(?:\\\\[\s\S][^"\\\\]*)*"|\'[^\'\\\\]*(?:\\\\[\s\S][^\'\\\\]*)*\')
      |(?://|\#)[^\n]*(?=(?:\n|$)) | /\*(?:[^*]|\*(?!/))*\*/ | (?:[a-zA-Z0-9_]\w*))',
    'structures' => [
      ['([^{}]*)([{}])', '(^|[^NS\s>.$]|[^-]>)[NS\s]*F[NS\s]*\(', '{', '}B'], // closures
      ['([^{}]*)([{}])', '(^|[^NS\s>.$]|[^-]>)[NS\s]*X[NS\s]*$', '{', '}Z'], // do-while block
      ['([^()]*)([()])', '(^|[^ZNS\s>.$]|[^-]>)[NS\s]*[WX][NS\s]*$', '(', ')Z'], // other blocks
    ],
    'tokens' => [
      'A' => 'case|default',
      'D' => '[0-9].*',
      'E' => 'self',
      'F' => 'function',
      'G' => 'as',
      'I' => '[\'"]_*[a-z][a-zA-Z0-9_]*[\'"]',
      'J' => 'and|extends|implements|instanceof|insteadof|x?or',
      'K' => 'break|continue|die|end(?:declare|for(?:each)?|if|switch|while)
        |exit|false|null|return|true',
      'L' => 'callable|class|clone|const|declare|echo|else|global|goto
        |include(?:_once)?|interface|new|print|private|require(?:_once)?
        |throw|trait|try|var|yield|array|binary|bool(?:ean)?|double|float
        |int(?:eger)?|object|real|string|unset',
      'M' => 'private|protected|public|final|abstract',
      'O' => 'namespace|use',
      'P' => '<\?php',
      'R' => 'parent',
      'S' => '[/\#][\w\W]*',
      'T' => 'this',
      'U' => 'static',
      'W' => 'catch|elseif|for(?:each)?|if|switch|while',
      'X' => 'do',
      'V' => '_*[a-z]\w*|GLOBALS|_SERVER|_REQUEST|_POST|_GET|_FILES|_ENV|_COOKIE|_SESSION',
      'Q' => '[\'"][\w\W]*',
      'C' => '_*[A-Z].*',
      // N: empty, H: html, Y: string without quotes, B: posible semicolon, Z: no semicolon
    ],
    'dictionary' => [
      'as' => 'G',
      'case' => 'A',
      'class' => '[CERU]',
      'eol' => '(?:\n|$)',
      'eos' => '[S\s]*(\n|$)(?![S\s]*([\])\.\-+:=/%*&|>,\{?GJ]|<[^?]))',
      'function' => 'F',
      'html' => 'H',
      'key' => '[AEFGJKLMOPRTUVWX]',
      'keyword' => '[AEFGJKLMOPRUWX]',
      'leading' => '[FGJLOWX]',
      'methodprefix' => '[MU][MSU\s]*',
      'noesc' => '^|[^\\\\]',
      'noprop' => '^|[^>$\\\\]|[^-]>',
      'ns' => '[O\\\\]',
      'phptag' => 'P',
      'self' => 'E',
      'space' => '[NS\s]',
      'string' => '[QI]',
      'this' => 'T',
      'var' => '[TV]',
    ],
    'toPhp' => [
      '(noesc)@@' => '$1self::', // @@ to self
      '(noesc)@' => '$1$this->', // @ to $this
      '(ns,space*)var|var(space*\\\\)' => '$1C$2', // mark namespaces
      '(class,space*as,space*)var' => '$1C', // mark namespaces
      '(([$.]|->) keyword)' => '$2V', // mark variables
      '(^|[^\?:S\s\\\\]):(space)' => '$1 =>$2', // colons to double arrows
      '(^|[,[(]space*)key(\s?=>)' => "$1'I'$2", // quote array keys
      '([^.])\.(?![.=D])' => '$1->', // dots to arrows
      '(class)->' => '$1::', // arrows to two colons
      '(noesc)~' => '$1.', // tilde to single dot
      '(noprop)(var(?!space*\())' => '$1$$2', // add dollars
      '([A-RT-Y\]\)\-\+]|[^\{;S\s]\})(eos)' => '$1;$2', // add trailing semicolons
      '(^space*|(?:noprop)leading|phptag|html);(space*eol)' => '$1$2', // no semicolons after leading keywords
      '(case[^\n]*)\s\=>' => '$1:', // no double arrows after cases
      '<\?(?!p[h][p]\\b|=)' => '<?php', // expand short tags
      '(methodprefix)(var,space*\()' => '$1function $2', // add functions
      '\\\\([~@$])' => '$1', // unescape @, tildes and dollars
    ],
    'toLady' => [
      '([@~])' => '\\\\$1', // escape @ and tildes
      '(->|\$)\$' => '$1\\\\$', // escape dollars before dynamic variables
      '(^|[,[(]space*)(\$var,space*=>)' => '$1\\\\$2', // escape dollars before keys
      '\$this->' => '@N', // $this to @
      'self::' => '@@N', // self to @@
      '([^.])\.(?![.=D])' => '$1~', // dots to tilde
      '->' => '.', // arrows to dots
      '(class)::' => '$1.', // double colons to dots
      '(noesc)\$(var(?!space*\())' => '$1$2', // remove dolars
      '\$(keyword)' => '$V', // mark variables
      'I(\s?=>)' => 'Y$1', // unquote array keys
      '(^|[^S\s])\s?=>(\s)' => '$1:$2', // double arrows to colons
      '(phptag)' => 'N<?', //convert long opening tag to short tag
      '(methodprefix)function(?:space)(space*var)' => '$1N$2', // remove functions
      '\\\\\\$' => '$', // unescape dollars before keywords
      ';(space*eol)' => '$1', // remove trailing semicolons
    ]
  ];

  public static function toPhp($input){
    return self::convert($input);
  }

  public static function toLady($input){
    return self::convert($input, true);
  }

  /**
   * Converts between php and ladyphp code.
   */
  protected static function convert($code, $toLady = false) {
    $rules = self::$rules[$toLady ? 'toLady' : 'toPhp'];
    $values = $brackets = [];
    $parser = sprintf('{%s}x', self::$rules['parser']);
    $code = preg_replace_callback($parser, function ($m) use (&$values) {
      $values[] = $m[0];
      if (isset($m[1])) return 'H';
      foreach (self::$rules['tokens'] as $name => $pattern) {
        if (preg_match(sprintf('{^(%s)$}x', $pattern), $m[0])) return $name;
      }
    }, $code);
    foreach (self::$rules['structures'] as $struct) {
      $code = preg_replace_callback(sprintf('/%s/', $struct[0]), function ($m) use (&$brackets, $struct) {
        if ($m[2] == $struct[2]) {
          $brackets[] = (preg_match(sprintf('{%s}', $struct[1]), $m[1]) == 1);
          return $m[0];
        } else {
          return $m[1] . (array_pop($brackets) ? $struct[3] : $m[2]);
        }
      }, $code);
    }
    $patterns = preg_replace_callback('/([a-z]{2,}),?/', function ($m) {
      return self::$rules['dictionary'][$m[1]];
    }, array_keys($rules));
    $patterns = preg_replace('/^.*/s', '{$0}x', $patterns);
    $code = preg_replace($patterns, $rules, $code);
    return preg_replace_callback('/[A-Z]/', function ($m) use (&$values) {
      if ($m[0] == 'B' || $m[0] == 'Z') return '';
      $value = array_shift($values);
      return ($m[0] == 'N') ? '' : ($m[0] == 'Y' ? substr($value, 1, -1) : $value);
    }, $code);
  }
}

