<?php

set_time_limit(60);

include "classes/lib.php";

$dataset = 'canadian-41st-election-ranked.csv';

$ridings = array();

$h = fopen($dataset, 'r');
if (empty($h)) {
  die();
}

$list = array();
$riding = NULL;
$poll = NULL;
$last = NULL;

$skip = 1;
while (($line = fgetcsv($h)) !== FALSE) {
  if ($skip) { $skip--; }
  else {
    if (!empty($line[1])) {
      if ($line[1] !== $last) {
        $riding = new RidingResults($line[1], $line[0]);
        $list[] = $riding;
        $poll = new PollResults($line[1]);
        $riding->addPollResult($poll);
        $last = $line[1];
      }
      $vote = new OrdinalVote(trim($line[3]) * 1);
      $order = explode('>', $line[2]);
      $rank = 1;
      foreach($order as $party) {
        $party = trim($party);
        $vote->addRanking($party, $rank);
        $rank++;
      }
      $poll->addVote($vote);
    }
  }
}

fclose($h);


set_time_limit(120);

$pluralityContest = new SingleWinnerContest(new Plurality());
$results = $pluralityContest->election($list);
echo '<h2>Plurality</h2>';
var_dump($results);


$irvContest = new SingleWinnerContest(new CoombsMethod());
$results = $irvContest->election($list);
echo '<h2>Coombs</h2>';
var_dump($results);

$irvContest = new SingleWinnerContest(new InstantRunoff());
$results = $irvContest->election($list);
echo '<h2>Instant Runoff</h2>';
var_dump($results);

$bordaContest = new SingleWinnerContest(new BordaCount(BORDA_DIMINISHING));
$results = $bordaContest->election($list);
echo '<h2>Borda Count</h2>';
var_dump($results);

$kyContest = new SingleWinnerContest(new CondorcetMultipleMethod(new InstantRunoff()));
$results = $kyContest->election($list);
echo '<h2>Condorcet/IRV Method</h2>';
var_dump($results);


echo '<h2>Proportional Representation</h2>';
$contest = new ProportionalContest();
$results = $contest->election($list);
var_dump($results);

