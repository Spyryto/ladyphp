<?php

class Lady {
  protected static $patterns = array(
    'methodPrefix' => '\b(?:private|protected|public)(?:\s+ static)?\s+',
    'keywords' => 'abstract|and|as|break|callable|case|catch|class|clone|const
      |continue|declare|default|do|echo|else|elseif|end|enddeclare|endfor
      |endforeach|endif|endswitch|endwhile|extends|false|final|for|foreach
      |function|global|goto|if|implements|include|include_once|instanceof
      |insteadof|interface|namespace|new|null|or|print|private|protected|public
      |require|require_once|return|self|static|switch|throw|trait|true|try|use
      |var|while|xor|yield',
    'classId' => '\b(?:self|static|[A-Z][\w\d\_]*)\b',
    'varId' => '\b[a-z\_][\w\d\_]*\b',
    'ignoredTokens' => '{^T_((DOC_|ML_)?COMMENT|INLINE_HTML)$}',
    'codeAndString' => '{([^"\']*)?("[^"\\\\]*(\\\\.[^"\\\\]*)*"
      |\'[^\'\\\\]*(\\\\.[^\'\\\\]*)*\')?}x',
  );

  public static function toPhp($input){
    extract(self::$patterns);
    return self::convert($input, array(
      '~ \.([\w_]) ~x' => '->\1', // dots to arrows
      '~ \.(\.|\-\>) ~x' => '.', // duplicated dots to single dot
      '~ ([^\\\\]|^) @@ ~x' => '\1self::', // @@ to self
      '~ ([^\\\\]|^) @ ~x' => '\1$this->', // @ to $this
      '~ \\\\@ ~x' => '@', // unescape @
      "~ ({$classId}) \-> ~x" => '\1::', // arrows to two colons
      "~ ([^>\\\$]|^) ({$varId} (?!\\()) ~x" => '\1\$\2', // add dollars
      '~ ([^\s]|^) \: (\s) ~x' => '\1 =>\2', // colons to double arrows
      "~ \\$({$keywords}) \b ~x" => '\1', // remove dollars from keywords
      '~ \<\?\$php \b ~x' => '<?php', // remove dollars from opening tags
      "~^ (\s* function \s*) \\$ ~xm" => '\1', // remove dollars from function names
      '~ <\? (?!php\b) ~x' => '<?php', // convert short opening tag to long tag
      "~ ({$methodPrefix}) ({$varId} \s* \() ~x" => '\1function \2', // add function to methods
    ));
  }

  public static function toLady($input){
    extract(self::$patterns);
    return self::convert($input, array(
      '~ @ ~x' => '\\@', // escape @
      '~ \$this\-> ~x' => '@', // $this to @
      '~ \b self:: ~x' => '@@', // self to @
      '~ \. (?!\=) ~x' => '..', // dots to double dots
      '~ (\-\>|::) ~x' => '.', // arrows to dots
      '~ \$ ([\w\d\_]+ \s* ([^\w\_\s\(] | $) ) ~x' => '\1', // remove dolars
      '~ \s? => ~x' => ':', // double arrows to colons
      '~ <\?php \b ~x' => '<?', //self::convert long opening tag to short tag
      "~ ({$methodPrefix}) function \s+ ({$varId} \s* \() ~x" => '\1\2', // remove function from methods
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
