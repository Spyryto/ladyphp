<?php

class Fruit {
  private $apples = 0;
  private $numbers = [
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
    $apples = $this->apples;
    $out = 'You have ';
    $out .= isset($this->numbers[$apples])
           ? $this->numbers[$apples] : $apples;
    switch ($apples) {
      case 1;
        return $out . ' apple.';
      default;
        return "$out apples.";
    }
  }
}

$fruit = new Fruit();

$anonym = function() use ($fruit) {
  $fruit->addApples(1)
       ->addApples(2);
};

$anonym();

echo "<p>" . $fruit->countApples() . "</p>";

Cls::func();
Cls::$v;
