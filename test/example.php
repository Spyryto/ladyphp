<?php
namespace my\app;
use my\app as app2;

/*
  Multiline
  comment
*/
class Fruit {
  private $apples = 0; // we have nothing now
  private static $numbers = [ # english numbers
    1 => 'one',
    'x' => 'two',
  ];

  public function addApples($n = 0) {
    if ($n >= 0) {
      $this->apples += $n;
    }
    return $this;
  }

  public function countApples() {
    $out = 'You have ';
    $out .= isset(self::$numbers[$this->apples]) ? self::$numbers[$this->apples] : $this->apples;
    switch ($this->apples) {
      case 1:
        return $out . ' apple.';
      default:
        return "$out apples.";
    }
    $this->addApples(0);
    @self::staticMethod();
  }

  public static function staticMethod () {
    $try = (double) 0.42;
  }
}

$fruit = new Fruit();

$anonym = function(...$args) use ($fruit) {
  $fruit->addApples(1)
       ->addApples(2);
};

$anonym();

?><p><?php echo $fruit->countApples() ?></p><?php
?><p><?= $fruit->countApples() ?></p><?php

@Cls::func();
Cls::$v;
$a = $a ?: $b;
$a = ~$b;
$a->$x;

$class();
$this->class;
parent::$a;
$obj->parent->a;
$list = [
  'x' => 0,
  $y => 1
];

foreach ($list as $x => $y)
{
  echo "$x: $y";
}
if ($c == ($a + $b))
  echo "single line";
do
{
  echo "loop";
} while ($c);
