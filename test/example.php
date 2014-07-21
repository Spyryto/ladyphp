<?php

class Fruit {
  private $apples = 0;
  private static $numbers = [
    1 => 'one',
    2 => 'two',
    3 => 'three'
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
      case 1;
        return $out . ' apple.';
      default;
        return "$out apples.";
    }
    $this->addApples(0);
    @self::staticMethod();
  }

  public static function staticMethod() {}
}

$fruit = new Fruit();

$anonym = function() use ($fruit) {
  $fruit->addApples(1)
       ->addApples(2);
};

$anonym();

echo "<p>" . $fruit->countApples() . "</p>";

@Cls::func();
Cls::$v;
