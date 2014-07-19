<?php

class Lady {
  protected static $regexps = array(
    'methodPrefix' => '\b(?:private|protected|public)(?:\s+ static)?\s+',
    'keywords' => 'catch|elseif|for|foreach|if|switch|try|while
      |end|abstract|and|as|break|callable|case|clone|const
      |continue|declare|default|echo|enddeclare|endfor|endforeach|endif
      |endswitch|endwhile|extends|final|global|goto|implements|include
      |include_once|instanceof|insteadof|interface|namespace|new|or|print
      |require|require_once|return|static|throw|trait|use|var|xor|yield
      |class|do|else|function|private|protected|public',
    'classId' => '\b[A-Z][\w\d\_]*\b',
    'varId' => '\b[a-z\_][\w\d\_]*\b'
  );

  public static function toPhp($input){
    extract(self::$regexps);
    $rules = array(
      '~ \.([\w_]) ~x' => '->\1', // dots to arrows
      '~ \.(\.|\-\>) ~x' => '.', // two dots to dot
      "~ ({$classId}) \-> ~x" => '\1::', // arrows to two colons
      "~ ([^>\\\$]|^) ({$varId} (?!\\()) ~x" => '\1\$\2', // add dollars
      '~ ([^\s]|^) \: (\s) ~x' => '\1 =>\2', // colons to double arrows
      "~ \\$({$keywords}) \b ~x" => '\1', // remove dollars from keywords
      "~^ (\s* function \s*) \\$ ~xm" => '\1', // remove dollars from function names
      '~ <\? (?!php\b) ~x' => '<?php', // convert short opening tag to long tag
      "~ ({$methodPrefix}) ({$varId} \s* \() ~x" => '\1function \2', // add function to methods
    );
    return self::convert($input, $rules);
  }

  public static function toLady($input){
    extract(self::$regexps);
    $rules = array(
      '~ \. (?!\=) ~x' => '..', // dots to two dots
      '~ (\-\>|::) ~x' => '.', // arrows to dots
      '~ \$ ([\w\d\_]+ \s* ([^\w\_\s\(] | $) ) ~x' => '\1', // remove dolars
      '~ \s? => ~x' => ':', // double arrows to colons
      '~ <\?php \b ~x' => '<?', //self::convert long opening tag to short tag
      "~ ({$methodPrefix}) function \s+ ({$varId} \s* \() ~x" => '\1\2', // remove function from methods
    );
    return self::convert($input, $rules);
  }

  protected static function convert($input, $rules) {
    $codePattern = '~^ ([^"\']*)? ("[^"\\\\]*(\\\\.[^"\\\\]*)*"'
      . '|\'[^\'\\\\]*(\\\\.[^\'\\\\]*)*\')? ~x';
    $output = '';
    $rules += array('~ \s+$ ~' => ''); // trailing spaces
    while (mb_strlen($input) > 0) {
      preg_match($codePattern, $input, $match);
      $match += array_fill(0, 3, '');
      list($code, $string) = array_slice($match, 1, 2);
      $output .= preg_replace(array_keys($rules), $rules, $code) . $string;
      $input = mb_substr($input, mb_strlen($match[0]));
    }
    return $output;
  }
}
