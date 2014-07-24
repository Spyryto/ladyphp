<?php

class Lady {
  public static $rules = [
    'keywords' => 'abstract|and|as|break|callable|case|catch|class|clone|const
      |continue|declare|default|do|echo|else(if)?|end(declare|for(each)?|if
      |switch|while)?|extends|false|final|for(each)?|function|global|goto|if
      |implements|include(_once)?|instanceof|insteadof|interface|namespace|new
      |null|or|parent|print|private|protected|public|require(_once)?|return
      |self|static|switch|throw|trait|true|try|use|var|while|xor|yield|array
      |binary|bool(ean)?|double|float|int(eger)?|object|real|string|unset',
    'methodPrefix' => '\\b(?:private|protected|public)(?:\\s+ static)?\\s+',
    'classId' => '(^|[^>$]|[^-]>) \\b(?:self|static|parent|[A-Z]\\w*|_+[A-Z])\\b',
    'varId' => '\\b (?:[a-z]\\w*|_+[a-z]\\w*|GLOBALS|_SERVER|_REQUEST|_POST|_GET
      |_FILES|_ENV|_COOKIE|_SESSION) \\b',
    'toPhp' => [
      '\\$' => '\\\\$', // escape dollars
      '(^|[^\\\\]) @@' => '$1self::', // @@ to self
      '(^|[^\\\\]) @' => '$1$this->', // @ to $this
      '\\.([^.=0-9])' => '->$1', // dots to arrows
      '\\.(\\.|->)' => '.', // duplicated dots to single dot
      '({classId}) ->' => '$1::', // arrows to two colons
      '(^|[^>$\\\\]) ({varId} (?!\s*\\() )' => '$1$$2', // add dollars
      '(^|[^\\\\]) \\$ ({keywords}) \\b' => '$1$2', // remove dollars from keywords
      '<\\?\\$php \\b' => '<?php', // remove dollars from opening tags
      '(^|[^\\?\\s\\\\]) : (\\s)' => '$1 =>$2', // colons to double arrows
      '(\\b (case|default) \\b [^\\v]*) \\s =>' => '$1:', // remove double arrows from cases
      '<\\? (?!php\\b|=)' => '<?php', // convert short opening tag to long tag
      '({methodPrefix}) ({varId} \\s*\\( )' => '$1function $2', // add functions
      '\\\\@' => '@', // unescape @
      '\\\\ \\$' => '$', // unescape dollars
      '\\\\:' => ':', // unescape colons
    ],
    'toLady' => [
      '@' => '\\@', // escape @
      '(->) \\$' => '$1\\\\$', // escape dollars before dynamic properties
      '\\$\\$' => '\\\\$\\\\$', // escape dollars before dynamic variables
      '\\$ ({keywords}) \\b' => '\\\\$$1', // escape dollars before keywords
      '(?m) (^|[^\\s\?]) : (\\s)' => '$1\\:$2', // escape colons after cases
      '\\$this->' => '@', // $this to @
      '\\b self::' => '@@', // self to @@
      '\\. (?![=0-9])' => '..', // dots to double dots
      '->' => '.', // arrows to dots
      '({classId}) ::' => '$1.', // double colons to dots
      '(^|[^\\\\]) \\$ ({varId} \\b (?!\\s*\\() )' => '$1$2', // remove dolars
      '(^|[^\\s]) \\s? => (\\s)' => '$1:$2', // double arrows to colons
      '<\\?php \\b' => '<?', //self::convert long opening tag to short tag
      '({methodPrefix}) function \\s+ ({varId} \\s*\\()' => '$1$2', // remove functions
      '\\\\ \\$' => '$', // unescape dollars before keywords
    ],
    // patterns for inline html, strings and comments
    'tokens' => '^(?: (?: \\?> (?:[^<]|<[^?])* (<\\?(?:php\\b)?)? )
      |(?: "[^"\\\\]*(?:\\\\[\\s\\S][^"\\\\]*)*" | \'[^\'\\\\]*(?:\\\\[\\s\\S][^\'\\\\]*)*\' )
      |(?: //[^\\n]*\\n | /\\* (?:[^*]|\\*(?!/))* \\*/) )',
  ];

  public static function toPhp($input){
    return self::convert($input, self::$rules['toPhp']);
  }

  public static function toLady($input){
    return self::convert($input, self::$rules['toLady']);
  }

  /**
   * Converts between php and ladyphp code.
   */
  protected static function convert($input, $rules) {
    $output = $code = '';
    $input = '?>' . $input;
    $tokensPattern = sprintf('{%s}x', self::$rules['tokens']);
    while (!empty($input) || !empty($code)) {
      if (preg_match($tokensPattern, $input, $matches) || empty($input)) {
        list($token, $phpTag) = $matches + array_fill(0, 2, '');
        $token = $phpTag ? substr($token, 0, -strlen($phpTag)) : $token;
        $output .= ($code ? self::convertCodeToken($code, $rules) : '') . $token;
        $input = substr($input, strlen($token));
        $code = '';
      } else {
        $code .= $input[0];
        $input = substr($input, 1);
      }
    }
    return substr($output, 2);
  }

  /**
   * Applies rules to parts of code (without html, strings and comments)
   */
  protected static function convertCodeToken($input, $rules) {
    $patterns = preg_replace_callback('~{(\w+)}~', function ($m) {
      return self::$rules[$m[1]];
    }, array_keys($rules));
    $patterns = preg_replace('{^.*$}s', '{\0}x', $patterns);
    return preg_replace($patterns, $rules, $input);
  }
}
