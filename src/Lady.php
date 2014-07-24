<?php

class Lady {
  protected static $rules;

  protected static function getRules($key) {
    if (!self::$rules) {
      self::$rules = require(__DIR__ . '/rules.php');
    }
    return self::$rules[$key];
  }

  public static function toPhp($input){
    return self::convert($input, self::getRules('toPhp'));
  }

  public static function toLady($input){
    return self::convert($input, self::getRules('toLady'));
  }

  /**
   * Converts between php and ladyphp code.
   */
  protected static function convert($input, $rules) {
    $output = $code = '';
    $input = '?>' . $input;
    $tokensPattern = sprintf('{%s}x', self::getRules('tokens'));
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
      return self::getRules($m[1]);
    }, array_keys($rules));
    $patterns = preg_replace('{^.*$}s', '{\0}x', $patterns);
    return preg_replace($patterns, $rules, $input);
  }
}
