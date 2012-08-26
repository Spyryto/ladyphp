
LadyPHP - type PHP with elegance
================================

Simple (and stupid) preprocessor for PHP. Main purpose of this is making source code a little more beautiful.

- optional `;` at end of line
- variables doesn't have to be prefixed with `$`, but it must starts with a lowercase letter
- indent style, no need for `{` and `}`
- `.` is converted to `->` or `::`, but not if it's surrounded by spaces
- `:` is converted to `=>`, but only if there isn't space before it
- `fn foo()` is converted to `function foo()`
- `Foo\Bar()` is converted to `new Foo\Bar()`
- optional `:` after `case ...` and `default`
- `<?` and `<?=` are converted to `<?php` and `<?php echo`
- original line numbers are preserved (handy for debugging)
- Lady herself is written in Lady, use the source for reference

## Usage

    <?php
    require_once('lady.php');
    Lady::includeFile('example.lady', 'cache/example.php');

## Example

#### LadyPHP

    <?

    /**
     * Example class
     */
    class Fruit

      # variables
      var apples = 0
      var numbers = [
        1: 'one',
        2: 'two',
        3: 'three',
      ]

      // add some apples
      fn addApples(n = 1)
        if (n >= 0)
          this.apples += n
        return this

      /* count your apples */
      fn countApples()
        apples = this.apples
        out = 'You have '
        out .= isset(this.numbers[apples]) ? this.numbers[apples] : apples
        switch (apples)
          case 1
            return out . ' apple.'
          default
            return "$out apples."

    fruit = Fruit()
    fruit.addApples(1)
         .addApples(2)
    ?>
    <p><?=fruit.countApples()?></p>

#### PHP

    <?php # DO NOT EDIT THIS FILE. It was generated by LadyPHP.

    /**
     * Example class
     */
    class Fruit{

      # variables
      var $apples = 0;
      var $numbers = [
        1 => 'one',
        2 => 'two',
        3 => 'three',
      ];

      // add some apples
      function addApples($n = 1){
        if ($n >= 0){
          $this->apples += $n;}
        return $this;}

      /* count your apples */
      function countApples(){
        $apples = $this->apples;
        $out = 'You have ';
        $out .= isset($this->numbers[$apples]) ? $this->numbers[$apples] : $apples;
        switch ($apples){
          case 1;
            return $out . ' apple.';
          default;
            return "$out apples.";}}}

    $fruit = new Fruit();
    $fruit->addApples(1)
         ->addApples(2);
    ?>
    <p><?php echo $fruit->countApples()?></p>

#### Output

    <p>You have three apples.</p>

## API

### Flags

- `Lady::COMPRESS` - compress output php code (remove whitespace)
- `Lady::NOCACHE` - always overwrite cache file

### Lady::parse()

    Lady::parse(string $source, int $flags = 0)

Convert LadyPHP from string to PHP code.

### Lady::parseFile()

    Lady::parseFile(string $file, string $cacheFile = null, int $flags = 0)

Convert LadyPHP from file to PHP code.

### Lady::includeFile()

    Lady::includeFile(string $file, string $cacheFile = null, int $flags = 0)

If `cacheFile` is null, convert LadyPHP from file and execute output.

If `cacheFile` is set, then check if `cacheFile` if newer then `file`. If it's older, parse `file` and save output to `cacheFile`. Then include `cacheFile`. 

### Lady::testFile()

    Lady::testFile(string $file, int $flags = 0)

Parse file and show input and output as html.

### Lady::compress()

    Lady::compress(string $php)

Helper function to remove whitespaces from PHP code.
