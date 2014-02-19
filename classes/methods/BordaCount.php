<?php

define('BORDA_SIMPLE', 'simple');
define('BORDA_OFFSET', 'offset');
define('BORDA_DOUBLE', 'double');
define('BORDA_TRIPLE', 'triple');
define('BORDA_DIMINISHING', 'diminishing');
define('BORDA_MODIFIED', 'modified');

class BordaCount extends GenericElectionMethod {
  
  private $bordaCountMethod = BORDA_SIMPLE;
  
  public function __construct($countMethod = BORDA_SIMPLE) {
    $this->bordaCountMethod = $countMethod;
  }
  
  public function elect(array $votes, $reset = TRUE) {
    if ($reset) {
      $this->resetExcludedCandidates();
    }
    $results = array();
    $max = $this->getNumberOfCandidates($votes);
    foreach ($votes as $vote) {
      if ($vote instanceof OrdinalVote) {
        $rankings = $vote->getRankings();
        foreach ($rankings as $rank => $candidates) {
          foreach ($candidates as $candidate) {
            if (!isset($results[$candidate])) {
              $results[$candidate] = 0;
            }
            $points = $this->getPoints($rank, $max, count($candidates)) * $vote->getWeight();
            $results[$candidate] += $points;
          }
        }
      }
    }
    arsort($results);
    foreach ($this->getExcludedCandidates() as $candidate) {
      unset($results[$candidate]);
    }
    return $results;
  }
  
  private function getPoints($rank, $most, $actual) {
    switch ($this->bordaCountMethod) {
      case BORDA_SIMPLE:
        return $most - $rank;
        break;
      case BORDA_DOUBLE:
        return 2 * ($most - $rank);
      case BORDA_TRIPLE:
        return 3 * ($most - $rank);
      case BORDA_OFFSET:
        return $most - $rank - 1;
      case BORDA_DIMINISHING:
        return 1 / ($rank + 1);
      case BORDA_MODIFIED:
        return $actual - $rank;
    }
  }
  
}