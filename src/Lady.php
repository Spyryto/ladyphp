<?php

class Lady {
  protected static $patterns = array(
    'methodPrefix' => '\b(?:private|protected|public)(?:\s+ static)?\s+',
    'keywords' => 'abstract|and|as|break|callable|case|catch|class|clone|const
      |continue|declare|default|do|echo|else(if)?|end(declare|for(each)?|if
      |switch|while)?|extends|false|final|for(each)?|function|global|goto|if
      |implements|include(_once)?|instanceof|insteadof|interface|namespace|new
      |null|or|parent|print|private|protected|public|require(_once)?|return
      |self|static|switch|throw|trait|true|try|use|var|while|xor|yield|array
      |binary|bool(ean)?|double|float|int(eger)?|object|real|string|unset',
    'classId' => '(?<!->) (?<!\$) \b(?:self|static|parent|[A-Z]\w*|_+[A-Z])\b',
    'varId' => '\b (?:[a-z]\w*|_+[a-z]\w*|GLOBALS|_SERVER|_REQUEST|_POST|_GET
      |_FILES|_ENV|_COOKIE|_SESSION) \b',
    'ignoredTokens' => '{^T_((DOC_|ML_)?COMMENT|INLINE_HTML)$}',
    'codeAndString' => '{([^"\']*)?("[^"\\\\]*(\\\\.[^"\\\\]*)*"
      |\'[^\'\\\\]*(\\\\.[^\'\\\\]*)*\')?}xs',
  );

  public static function toPhp($input){
    extract(self::$patterns);
    return self::convert($input, array(
      '\\$' => '\\\\$', // escape dollars
      '(^|[^\\\\]) @@' => '\1self::', // @@ to self
      '(^|[^\\\\]) @' => '\1$this->', // @ to $this
      '\.([^.=0-9])' => '->\1', // dots to arrows
      '\.(\.|->)' => '.', // duplicated dots to single dot
      "($classId) ->" => '\1::', // arrows to two colons
      "(^|[^>\$\\\\]) ($varId (?!\() )" => '\1\$\2', // add dollars
      "(^|[^\\\\]) \\\$ ($keywords) \b" => '\1\2', // remove dollars from keywords
      '<\?\$php \b' => '<?php', // remove dollars from opening tags
      '(?m)^ (\s* function \s*) \\$' => '\1', // remove dollars from function names
      '(^|[^\s\\\\]) : (\s)' => '\1 =>\2', // colons to double arrows
      '(\b case \b [^\v]*) \s =>' => '\1:', // remove double arrows from cases
      '<\? (?!php\b)' => '<?php', // convert short opening tag to long tag
      "($methodPrefix) ($varId \s*\( )" => '\1function \2', // add functions
      '\\\\@' => '@', // unescape @
      '\\\\ \$' => '$', // unescape dollars
      '\\\\:' => ':', // unescape colons
    ));
  }

  public static function toLady($input){
    extract(self::$patterns);
    return self::convert($input, array(
      '@' => '\\@', // escape @
      '(->) \$' => '\1\\\\$', // escape dollars before dynamic properties
      '\$\$' => '\\\\$\\\\$', // escape dollars before dynamic variables
      "\\$ ($keywords) \b" => '\\\\$\1', // escape dollars before keywords
      '(?m) (^|[^\s]) : (\s)' => '\1\\\\:\2', // escape colons after cases
      '\$this->' => '@', // $this to @
      '\b self::' => '@@', // self to @@
      '\. (?![=0-9])' => '..', // dots to double dots
      '->' => '.', // arrows to dots
      "($classId) ::" => '\1.', // double colons to dots
      "(^|[^\\\\]) \\$ ($varId \b (?!\s*\() )" => '\1\2', // remove dolars
      '(^|[^\s]) \s? => (\s)' => '\1:\2', // double arrows to colons
      '<\?php \b' => '<?', //self::convert long opening tag to short tag
      "($methodPrefix) function \s+ ($varId \s*\()" => '\1\2', // remove functions
      '\\\\ \$' => '$', // unescape dollars before keywords
    ));
  }

  protected static function convert($input, $rules) {
    $tokens = token_get_all(self::expandShortTags($input));
    $tokens[] = array(false, '');
    $output = $code = '';
    foreach ($tokens as $token) {
      list($type, $text) = is_array($token) ? $token : array(true, $token);
      if ($type === false
          || preg_match(self::$patterns['ignoredTokens'], token_name($type))) {
        $output .= self::convertCodeToken($code, $rules) . $text;
        $code = '';
      } else {
        $code .= $text;
      }
    }
    return $output;
  }

  protected static function convertCodeToken($input, $rules) {
    $rules += array('\s+$ ~' => '');
    $patterns = preg_replace('{^.*$}s', '{\0}x', array_keys($rules));
    $output = '';
    while (mb_strlen($input) > 0) {
      preg_match(self::$patterns['codeAndString'], $input, $m);
      $m += array_fill(0, 3, '');
      $output .= preg_replace($patterns, $rules, $m[1]) . $m[2];
      $input = mb_substr($input, mb_strlen($m[0]));
      if (empty($m[0])) {
        throw new Exception('Cannot parse code: ' . $input);
      }
    }
    return $output;
  }

  protected static function expandShortTags($code) {
    if (function_exists('ini_get') && ini_get('short_open_tag')) return $code;
    do {
      $tokens = token_get_all($code);
      $tags = array(array('=', ' echo', 3), array('', '', 2));
      $code = $changed = null;
      foreach ($tokens as $n => $token) {
        if (!$changed && is_array($token) && $token[0] == T_INLINE_HTML) {
          foreach ($tags as $tag) {
            if (($pos = strpos($token[1], '<?'.$tag[0])) !== false) {
              $space = preg_match('{^[\s\v]}', mb_substr($token[1], $pos + 2, 1)) ? '' : ' ';
              $code .= substr_replace($token[1], '<?php'.$tag[1].$space, $pos, $tag[2]);
              $changed = true;
            }
          }
          $code .= $changed ? '' : $token[1];
        } else {
          $code .= is_array($token) ? $token[1] : $token;
        }
      }
    } while ($changed);
    return $code;
  }
}
