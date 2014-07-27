# LadyPHP: type PHP with elegance

Preprocessor for PHP, that allows you to write code with nicer syntax.

## LadyPHP Syntax

```
Lady       │ PHP
───────────┼────────────────────
x          │ $x
obj.x      │ $obj->x
Cls.x      │ Cls::$x
@x         │ $this->x
@@x        │ self::$x
obj.f()    │ $obj->f()
public f() │ public function f()
[1: 2]     │ [1 => 2]
x .. y     │ x . y
<?         │ <?php
```

To write error control operator `@`, you have to escape it with `\`.

## Usage from command line

```bash
ladyphp file.lady  # creates file.php
ladyphp file.php   # creates file.lady
ladyphp -w dir/    # watches directory and converts updated lady files
```

## Usage from PHP

```php
require_once('./Lady.php');
$php = Lady::toPhp($ladyCode)
$lady = Lady::toLady($phpCode)
```

## Usage from NodeJS

```javascript
var lady = require('./lady');
var php = lady.toPhp(ladyCode);
var lady = lady.toLady(phpCode);
```

## Todo

- plugin for text editors that does bidirectional conversion
- maybe include files with `Lady::requireFile('path/file.lady')`
- add more syntactic sugar
```
a ~ b  │ a . b
[a: b] │ array('a' => 'b')
```
