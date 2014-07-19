# LadyPHP: type PHP with elegance

Preprocessor for PHP, that allows you to write code with nicer syntax.

## LadyPHP Syntax

```
Lady       │ PHP
───────────┼────────────────────
var        │ $var
obj.var    │ $obj->var
obj.f()    │ $obj->f()
Cls.var    │ Cls::$var
public a() │ public function a()
<?         │ <?php
[1: 2]     │ [1 => 2]
a .. b     │ a . b
```

## API

```php
string Lady::toPhp(string $ladyCode)
string Lady::toLady(string $phpCode)
```

## Usage from command line

```bash
./bin/ladyphp -i file.lady    # creates file.php
./bin/ladyphp -l -i input.php -o output.lady
```

## Todo

- nicer command line API
- watchdog that converts all .lady files in directory to .php on the fly
- plugin for text editors that does bidirectional conversion
- PHP method that can include .lady files with `Lady::requireFile('path/file.lady')`
- PHP file stream that can include .lady files with `require('lady://path/file.lady')`
- add more syntactic sugar
```
@var       │ $this->var
@method()  │ $this->method()
a ~ b      │ a . b
[a: b]     │ array('a' => 'b')
```
