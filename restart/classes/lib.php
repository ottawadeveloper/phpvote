<?php

$base = dirname(__FILE__);

$classes = array(
  'AbstractBallot',
  'RankedBallot',
  'RatedBallot',
);
foreach ($classes as $class) {
  require $base . '/' . $class . '.php';
}
