<?php

$base = dirname(__FILE__);

include $base . "/classes/lib.php";

$votes = array();

$memphis = new OrdinalVote(4200);
$memphis->addRanking('Memphis', 1);
$memphis->addRanking('Nashville', 2);
$memphis->addRanking('Chattanooga', 3);
$memphis->addRanking('Knoxville', 4);

$votes[] = $memphis;

$nashville = new OrdinalVote(2600);
$nashville->addRanking('Nashville', 1);
$nashville->addRanking('Chattanooga', 2);
$nashville->addRanking('Knoxville', 3);
$nashville->addRanking('Memphis', 4);

$votes[] = $nashville;

$chattanooga = new OrdinalVote(1500);
$chattanooga->addRanking('Chattanooga', 1);
$chattanooga->addRanking('Knoxville', 2);
$chattanooga->addRanking('Nashville', 3);
$chattanooga->addRanking('Memphis', 4);

$votes[] = $chattanooga;

$knoxville = new OrdinalVote(1700);
$knoxville->addRanking('Knoxville', 1);
$knoxville->addRanking('Chattanooga', 2);
$knoxville->addRanking('Nashville', 3);
$knoxville->addRanking('Memphis', 4);

$votes[] = $knoxville;

$methods = array(
  'Plurality' => new Plurality(),
  'Instant Runoff' => new InstantRunoff(),
  'Contingent' => new InstantRunoff(TRUE),
  'Borda (Simple)' => new BordaCount(BORDA_SIMPLE),
  'Borda (Doubled)' => new BordaCount(BORDA_DOUBLE),
  'Borda (Tripled)' => new BordaCount(BORDA_TRIPLE),
  'Borda (Offset)' => new BordaCount(BORDA_OFFSET),
  'Borda (Modified)' => new BordaCount(BORDA_MODIFIED),
  'Borda (Diminishing)' => new BordaCount(BORDA_DIMINISHING),
  'Coombs' => new CoombsMethod(),
  'Supplementary Vote' => new InstantRunoff(TRUE, 2),
  'Bucklin Vote' => new BucklinVote(),
  'Sri Lankan Contingent Vote' => new InstantRunoff(TRUE, 3),
  'Condorcet (Smith)' => new CondorcetMultipleMethod(NULL, CONDORCET_SMITH_SET),
  'Condorcet (Schwartz)' => new CondorcetMultipleMethod(NULL, CONDORCET_SCHWARTZ_SET),
  'Condorcet (Copeland)' => new CopelandMethod(),
  'Condorcet (Kemeny-Young)' => new KemenyYoungMethod(),
);

ksort($methods);

echo '<table style="width: 500px;"><thead><tr><th style="text-align: left;">Method</th><th style="text-align: left;">Winner</th></tr></thead><tbody>';
foreach ($methods as $name => $method) {
  if ($method instanceof ElectionMethodInterface) {
    echo '<tr><th style="text-align: left;">' . $name . '</th>';
    $results = $method->elect($votes);
    $best = max($results);
    $winners = array();
    foreach ($results as $result => $v) {
      if ($v === $best) {
        $winners[] = $result;
      }
    }
    echo '<td>' . implode(', ', $winners) . '</td>';
    echo '</tr>';
  }
}
echo '</tbody></table';

