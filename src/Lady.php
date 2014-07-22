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
    'classId' => '(?<!\-\>) (?<!\$) \b (?:self|static|parent| [A-Z]\w* | _+[A-Z])\b',
    'varId' => '\b (?:[a-z]|_+[a-z]|GLOBALS|_SERVER|_REQUEST|_POST|_GET|_FILES
      |_ENV|_COOKIE|_SESSION) \w* \b',
    'ignoredTokens' => '{^T_((DOC_|ML_)?COMMENT|INLINE_HTML)$}',
    'codeAndString' => '{([^"\']*)?("[^"\\\\]*(\\\\.[^"\\\\]*)*"
      |\'[^\'\\\\]*(\\\\.[^\'\\\\]*)*\')?}xs',
  );

  public static function toPhp($input){
    extract(self::$patterns);
    return self::convert($input, array(
      '~ ([^\\\\]|^) @@ ~x' => '\1self::', // @@ to self
      '~ ([^\\\\]|^) @ ~x' => '\1$this->', // @ to $this
      '~ \.([^=0-9\.]) ~x' => '->\1', // dots to arrows
      '~ \.(\.|\-\>) ~x' => '.', // duplicated dots to single dot
      "~ ({$classId}) \-> ~x" => '\1\2::', // arrows to two colons
      '~ \\\\@ ~x' => '@', // unescape @
      "~ \\$ ~x" => "\\\\$", // escape dollars
      "~ ([^>\\\$\\\\]|^) ({$varId} (?!\\()) ~x" => '\1\$\2', // add dollars
      "~ ([^\\\\]|^) \\$({$keywords}) \b ~x" => '\1\2', // remove dollars from keywords
      '~ \<\?\$php \b ~x' => '<?php', // remove dollars from opening tags
      "~^ (\s* function \s*) \\$ ~xm" => '\1', // remove dollars from function names
      '~ \\\\ \$ ~x' => '$', // unescape dollars
      '~ ([^\s\\\\]|^) \: (\s) ~xm' => '\1 =>\2', // colons to double arrows
      '~ \\\\: ~x' => ':', // unescape colons
      '~ (\b case \b [^\v]*) \ => ~x' => '\1:', // remove double arrows from cases
      '~ <\? (?!php\b) ~x' => '<?php', // convert short opening tag to long tag
      // add function keywords before methods
      "~ ({$methodPrefix}) ({$varId} \s* \() ~x" => '\1function \2',
    ));
  }

  public static function toLady($input){
    extract(self::$patterns);
    return self::convert($input, array(
      '~ @ ~x' => '\\@', // escape @
      '~ (\-\>) \$ ~x' => '\1\\\\$', // escape dollars before dynamic properties
      '~ \$\$ ~x' => '\\\\$\\\\$', // escape dollars before dynamic variables
      "~ \\$ ({$keywords}) \b ~x" => '\\\\$\1', // escape dollars before keywords
      '~ ([^\s]|^) \: (\s) ~xm' => '\1\\\\:\2', // escape colons after cases
      '~ \$this\-> ~x' => '@', // $this to @
      '~ \b self:: ~x' => '@@', // self to @@
      '~ \. (?![=0-9]) ~x' => '..', // dots to double dots
      '~ \-\> ~x' => '.', // arrows to dots
      "~ ({$classId}) :: ~x" => '\1.', // double colons to dots
      "~ ([^\\\\]|^) \\$ ({$varId} \b (?!\s*\\() ) ~x" => '\1\2', // remove dolars
      '~ \\\\ \$ ~x' => '$', // unescape dollars before keywords
      '~ (^|[^\s]) \s? => (\s) ~x' => '\1:\2', // double arrows to colons
      '~ <\?php \b ~x' => '<?', //self::convert long opening tag to short tag
      // remove function keywords before methods
      "~ ({$methodPrefix}) function \s+ ({$varId} \s* \() ~x" => '\1\2',
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
    $rules += array('~ \s+$ ~' => '');
    $output = '';
    while (mb_strlen($input) > 0) {
      preg_match(self::$patterns['codeAndString'], $input, $m);
      $m += array_fill(0, 3, '');
      $output .= preg_replace(array_keys($rules), $rules, $m[1]) . $m[2];
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
