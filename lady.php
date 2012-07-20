<?php # vi:set ft=php:;

class Lady{


  # ---------------------------------------------;
  # constants
  # ---------------------------------------------
  const REGEX_CODE = '/.*[^(<\?|<\?php)\{\} ].*/';
  const REGEX_COMMENT = '/^ *(#|\/\/)/';
  const REGEX_EMPTY = '/^ *$/';
  const REGEX_END = '/(;|&|\|)$/';
  const REGEX_CONTINUE = '/^[&\|]/';
  const REGEX_END_OPENING = '/(^[^(]*|.*\))$/';
  const REGEX_SMALL = '/^[a-z].*/';
  const REGEX_NOVAR = '/^(false|true|self|null)$/';

  const PRESERVE = 0;
  const STRIP = 1;
  const COMPRESS = 2;


  # ---------------------------------------------;
  # parse
  # convert lady script to php
  # ---------------------------------------------
  static public function parse($source, $shrink = self::PRESERVE, $debug = false){
    $code = $dump = $noVar = null;

    # tokens;
    $tokens = token_get_all($source);
    foreach ($tokens as $n => $token){
      if (!is_array($token)){
        $tokens[$n][0] = $token;
        $tokens[$n][1] = $token;}
      else{
        $tokens[$n][0] = token_name($token[0]);
        $tokens[$n][1] = $token[1];}}
    
    foreach ($tokens as $n => $token){
      list($name, $string) = $token;
      if ($name == 'T_STRING'
      && $tokens[$n + 1][1] != '('
      && preg_match(self::REGEX_SMALL, $string)
      && !preg_match(self::REGEX_NOVAR, $string)){
        $code .= '$' . $string;}
      elseif ($name == 'T_COMMENT' && $shrink >= 1){
        if (substr($string, -1) == "\n"){
          $code .= "\n";}}
      else{
        $code .= $string;}
      $dump .= $n . '. ' . $name . ': ' . $string . "\n";}
    

    # lines;
    $lines = explode("\n", $code);
    $indent = 0;

    # shrink lines;
    $i = 0;
    foreach ($lines as $n => $line){
      if (!isset($emptyLines[$i])){
        $emptyLines[$i] = null;}
      if (preg_match(self::REGEX_CODE, $line)){
        $shrinkedLines[$i] = str_repeat('  ', $indent) . $line;
        $i++;}
      else{
        $emptyLines[$i] .= $line . "\n";}}
    $shrinkedLines[] = 'true;';
    $lines = $shrinkedLines;

    # edit lines;
    foreach ($lines as $n => $line){
      if (preg_match(self::REGEX_CODE, $line)
      && !preg_match(self::REGEX_CONTINUE, trim($line))){
        $indent_before = $indent;
        $indent = (strlen($line) - strlen(ltrim($line))) / 2;
        $jump = $indent - $indent_before;}
      else{
        $jump = 0;}
      
      $line = trim($line);
      if ($jump <= 0
      && $n > 0
      && !preg_match(self::REGEX_CONTINUE, $line)
      && preg_match(self::REGEX_CODE, $lines[$n - 1])
      && !preg_match(self::REGEX_COMMENT, $lines[$n - 1])
      && !preg_match(self::REGEX_END, $lines[$n - 1])
      && !preg_match(self::REGEX_EMPTY, $lines[$n - 1])){
        $lines[$n - 1] .= ';';}
      if ($jump > 0 && $n > 0
      && preg_match(self::REGEX_END_OPENING, $lines[$n - 1])){
        $lines[$n - 1] .= '{';}
      if ($jump < 0){
        $lines[$n - 1] .= str_repeat('}', -$jump);}

      $lines[$n] = str_repeat('  ', $indent) . $line;
      $lines[$n] = $emptyLines[$n] . $lines[$n];}

    $code = implode("\n", array_slice($lines, 0, -1));
    if ($shrink >= 2){
      $code = self::compress($code);}

    # output;
    return $debug ? $dump : $code;}
  

  # ---------------------------------------------;
  # parseFile
  # load file and parse it
  # ---------------------------------------------
  static public function parseFile($file, $shrink = self::PRESERVE, $debug = false){
    return self::parse(file_get_contents($file), $shrink, $debug);}
  

  # ---------------------------------------------;
  # includeFile
  # parse file and execute it
  # ---------------------------------------------
  static public function includeFile($file){
    ob_start();
    eval('?>' . self::parseFile($file));
    return ob_get_clean();}
  

  # ---------------------------------------------;
  # test
  # format html code from source and result
  # ---------------------------------------------
  static public function test($file, $shrink = self::PRESERVE, $debug = false){
    $source = file_get_contents($file);
    $output = self::parseFile($file, $shrink, $debug);
    $source = htmlspecialchars($source);
    $output = htmlspecialchars($output);
    return '<pre>' . $source . '</pre><hr><pre>' . $output . '</pre>';}


  # ---------------------------------------------;
  # compress
  # strip comments and compress php source
  # ---------------------------------------------
  static public function compress($input){
    if (!defined('T_DOC_COMMENT')){
      define('T_DOC_COMMENT', -1);}
    if (!defined('T_ML_COMMENT')){
      define('T_ML_COMMENT', -1);}

    $space = $output = '';
    $set = '!"#$&\'()*+,-./:;<=>?@[\]^`{|}';
    $set = array_flip(preg_split('//',$set));

    foreach (token_get_all($input) as $token){
      if (!is_array($token)){
        $token = array(0, $token);}

      if (in_array($token[0], array(T_COMMENT, T_ML_COMMENT, T_DOC_COMMENT, T_WHITESPACE))){
        $space = ' ';}
      else{
        if (isset($set[substr($output, -1)]) || isset($set[$token[1]{0}])){
          $space = '';}
        $output .= $space . $token[1];
        $space = '';}}
    return $output;}}