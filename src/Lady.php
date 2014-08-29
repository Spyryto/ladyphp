<?php

class Lady {
  public static $rules = [
    'parser' => '(?:(?:<\?php) | ((?:^|\?>) (?:[^<]|<[^?])* (?=<\?|$))
      |(?:"[^"\\\\]*(?:\\\\[\s\S][^"\\\\]*)*"|\'[^\'\\\\]*(?:\\\\[\s\S][^\'\\\\]*)*\')
      |(?://|\#)[^\n]*(?=\n) | /\*(?:[^*]|\*(?!/))*\*/ | (?:[a-zA-Z0-9_]\w*))',
    'closure' => '(^|[^>.$]|[^-]>)F[S\s]*\(',
    'tokens' => [
      'A' => 'case|default',
      'D' => '[0-9].*',
      'E' => 'self',
      'F' => 'function',
      'I' => '[\'"]_*[a-z][a-zA-Z0-9_]*[\'"]',
      'J' => 'and|as|extends|implements|instanceof|insteadof|x?or',
      'K' => 'break|continue|end(?:declare|for(?:each)?|if|switch|while)?
        |false|null|return|true',
      'L' => 'callable|catch|class|clone|const|declare|do|echo|else(?:if)?
        |for(?:each)?|global|goto|if|include(?:_once)?|interface|namespace
        |new|print|private|require(?:_once)?|switch|throw|trait|try|use|var
        |while|yield|array|binary|bool(?:ean)?|double|float|int(?:eger)?
        |object|real|string|unset',
      'M' => 'private|protected|public|final|abstract',
      'P' => '<\?php',
      'R' => 'parent',
      'S' => '[/\#][\w\W]*',
      'T' => 'this',
      'U' => 'static',
      'V' => '_*[a-z]\w*|GLOBALS|_SERVER|_REQUEST|_POST|_GET|_FILES|_ENV|_COOKIE|_SESSION',
      'H' => '[\'"][\w\W]*',
      'C' => '_*[A-Z].*',
      // N: empty, Y: string without quotes, B: closing bracket after closure
    ],
    'dictionary' => [
      'case' => 'A',
      'class' => '[CERU]',
      'eol' => '(?:\n|$)',
      'eos' => '[S\s]*(\n|$)(?![S\s]*([\])\.\-+:=/%*&|>,\{?]|<[^?]|J))',
      'function' => 'F',
      'key' => '[AEFJKLMPRTUV]',
      'keyword' => '[AFJKLMPRU]',
      'leading' => '[FJL]',
      'methodprefix' => '[MU][MSU\s]*',
      'noesc' => '^|[^\\\\]',
      'noprop' => '^|[^>$\\\\]|[^-]>',
      'phptag' => 'P',
      'self' => 'E',
      'space' => '[S\s]',
      'string' => '[HI]',
      'this' => 'T',
      'var' => '[TV]',
    ],
    'toPhp' => [
      '(noesc)@@' => '$1self::', // @@ to self
      '(noesc)@' => '$1$this->', // @ to $this
      '(([$\\\\.]|->) keyword)' => '$2V', // mark variables
      '(^|[^\?:S\s\\\\]):(space)' => '$1 =>$2', // colons to double arrows
      '(^|[,[(]space*)key(\s?=>)' => "$1'I'$2", // quote array keys
      '\.([^.=D])' => '->$1', // dots to arrows
      '(class)->' => '$1::', // arrows to two colons
      '(noesc)~' => '$1.', // tilde to single dot
      '(noprop)(var(?!space*\())' => '$1$$2', // add dollars
      '(string|[A-RT-Z\]\)\-\+]|[^\{;S\s]\})(eos)' => '$1;$2', // add trailing semicolons
      '((?:noprop)leading|P);(space* eol)' => '$1$2', // no semicolons after leading keywords
      '(case[^\n]*)\s\=>' => '$1:', // no double arrows after cases
      '<\?(?!ph[p]\\b|=)' => '<?php', // expand short tags
      '(methodprefix)(var,space*\()' => '$1function $2', // add functions
      '\\\\([~@$])' => '$1', // unescape @, tildes and dollars
    ],
    'toLady' => [
      '([@~])' => '\\\\$1', // escape @ and tildes
      '(->|\$)\$' => '$1\\\\$', // escape dollars before dynamic variables
      '(^|[,[(]space*)(\$var,space*=>)' => '$1\\\\$2', // escape dollars before keys
      '\$this->' => '@N', // $this to @
      'self::' => '@@N', // self to @@
      '\.(?![=D])' => '~', // dots to tilde
      '->' => '.', // arrows to dots
      '(class)::' => '$1.', // double colons to dots
      '(noesc)\$(var(?!space*\())' => '$1$2', // remove dolars
      '\$(keyword)' => '$V', // mark variables
      'I(\s?=>)' => 'Y$1', // unquote array keys
      '(^|[^S\s])\s?=>(\s)' => '$1:$2', // double arrows to colons
      'phptag' => 'N<?', //convert long opening tag to short tag
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
    $code = preg_replace_callback('/([^{}]*)([{}])/', function ($m) use (&$brackets) {
      if ($m[2] == '{') {
        $brackets[] = (preg_match(sprintf('{%s}', self::$rules['closure']), $m[1]) == 1);
        return $m[0];
      } else {
        return $m[1] . (array_pop($brackets) ? 'B' : $m[2]);
      }
    }, $code);
    $patterns = preg_replace_callback('/([a-z]{3,}),?/', function ($m) {
      return self::$rules['dictionary'][$m[1]];
    }, array_keys($rules));
    $patterns = preg_replace('/^.*/s', '{$0}x', $patterns);
    $code = preg_replace($patterns, $rules, $code);
    return preg_replace_callback('/[A-Z]/', function ($m) use (&$values) {
      if ($m[0] == 'B') return '}';
      $value = array_shift($values);
      return ($m[0] == 'N') ? '' : ($m[0] == 'Y' ? substr($value, 1, -1) : $value);
    }, $code);
  }
}

