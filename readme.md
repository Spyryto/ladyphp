# LadyPHP: type PHP with elegance

Preprocessor for PHP, that allows you to write code with nicer syntax.

## LadyPHP Syntax

```
Lady       │ PHP
───────────┼────────────────────
var        │ $var
obj.var    │ $obj->var
obj.fx()   │ $obj->fx()
@var       │ $this->var
@fx()      │ $this->fx()
@@var      │ self::$var
@@fx()     │ self::fx()
Cls.var    │ Cls::$var
public a() │ public function a()
<?         │ <?php
[1: 2]     │ [1 => 2]
a .. b     │ a . b
```

To write error control operator `@`, you have to escape it with `\`.

## API

```php
string Lady::toPhp(string $ladyCode)
string Lady::toLady(string $phpCode)
```

## Usage from command line

```bash
ladyphp file.lady  # creates file.php
ladyphp file.php   # creates file.lady\n"
ladyphp dir/       # convert updated lady files to php
```

## Todo

- watchdog that converts all .lady files in directory to .php on the fly
- plugin for text editors that does bidirectional conversion
- maybe: PHP method that can include .lady files with `Lady::requireFile('path/file.lady')`
- maybe: PHP file stream that can include .lady files with `require('lady://path/file.lady')`
- add more syntactic sugar
```
a ~ b      │ a . b
[a: b]     │ array('a' => 'b')
```
