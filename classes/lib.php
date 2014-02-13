<?php

$base = dirname(__FILE__);
$files = array(
  'votes/AbstractVote.php',
  'votes/CardinalVote.php',
  'votes/OrdinalVote.php',
  'methods/ElectionMethodInterface.php',
  'methods/AbstractElectionMethod.php',
  'methods/Plurality.php',
  'methods/InstantRunoff.php',
  'methods/BordaCount.php',
  'methods/CoombsMethod.php',
  'methods/AbstractCondorcetMethod.php',
  "methods/CondorcetMultipleMethod.php",
  'methods/BucklinVote.php',
  'methods/CopelandMethod.php',
  'methods/KemenyYoungMethod.php',
);

foreach ($files as $file) {
  require $base . '/' . $file;
}
