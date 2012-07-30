# LadyPHP - type PHP with elegance

Simple (and stupid) preprocessor for PHP. Main purpose of this is making source code a little more beautiful.

- optional `;` at end of line
- variables doesn't has to be prefixed with `$`, but it must starts with a lowercase letter
- indent style (2 spaces), no need for `{` and `}`
- `.` is converted to `->`, but not if it's surrounded by spaces
- `:` is converted to `=>`, but only if there isn't space before it
- `fn foo()` is converted to `function foo()`
- `Foo()` is converted to `new Foo()`
- original line numbers are preserved (handy for debugging)
- Lady herself is written in Lady, use the source for reference

## Example

#### LadyPHP

    <?

    class Fruit
      var apples = 0
      var numbers = [
        1: 'one',
        2: 'two',
        3: 'three',
      ]

      fn addApples(n = 1)
        this.apples += n
        return this

      fn countApples()
        apples = this.apples
        out = 'You have '
        out .= isset(this.numbers[apples]) ? this.numbers[apples] : apples
        if (this.apples == 1)
          return out . ' apple.'
        else
          return "$out apples."

    fruit = Fruit()
    fruit.addApples(1)
         .addApples(2)
    print fruit.countApples()

#### PHP

    <?php # DO NOT EDIT THIS FILE. It was generated by LadyPHP.

    class Fruit{
      var $apples = 0;
      var $numbers = [
        1 => 'one',
        2 => 'two',
        3 => 'three',];
      

      function addApples($n = 1){
        $this->apples += $n;
        return $this;}

      function countApples(){
        $apples = $this->apples;
        $out = 'You have ';
        $out .= isset($this->numbers[$apples]) ? $this->numbers[$apples] : $apples;
        if ($this->apples == 1){
          return $out . ' apple.';}
        else{
          return "$out apples.";}}}

    $fruit = new Fruit();
    $fruit->addApples(1)
         ->addApples(2);
    print $fruit->countApples();

#### Output

    You have three apples.

## Usage

    <?php
    require_once('lady.php');
    $php = Lady::parseFile('example.lady');

## API

### Shrink constants

- `Lady::PRESERVE` - preserve line numbers and comments
- `Lady::STRIP` - preserve line numbers, strip comments
- `Lady::COMPRESS` - compress output php code (remove whitespace)

### Lady::parse()

    Lady::parse(string $source, int $shrink = self::PRESERVE)

Convert LadyPHP from string to PHP code.

### Lady::parseFile()

    Lady::parseFile(string $file, string $cacheFile = null, int $shrink = self::PRESERVE)

Convert LadyPHP from file to PHP code.

### Lady::includeFile()

    Lady::includeFile(string $file, string $cacheFile = null, int $shrink = self::PRESERVE)

If `cacheFile` is null, convert LadyPHP from file and execute output.

If `cacheFile` is set, then check if `cacheFile` if newer then `file`. If it's older, parse `file` and save output to `cacheFile`. Then include `cacheFile`. 

### Lady::testFile()

    Lady::testFile(string $file, int $shrink = self::PRESERVE)

Parse file and show input and output as html.

### Lady::compress()

    Lady::compress(string $php)

Helper function to remove whitespaces from PHP code.


## What doesn't works

- multiline comments and inline html
- switch block
- namespaces (`new` is not added before `Foo\Bar()`)
