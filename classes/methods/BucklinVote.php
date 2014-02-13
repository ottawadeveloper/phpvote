<?php

class BucklinVote extends AbstractElectionMethod {
  
  private $limit = 0;
  
  public function __construct($maxRounds = 0) {
    $this->limit = $maxRounds;
  }
  
  public function elect(array $votes, $reset = TRUE) {
    if ($reset) {
      $this->resetExcludedCandidates();
    }
    $majority = $this->majorityThreshold($votes);
    $count = $this->getNumberOfCandidates($votes);
    $results = array();
    $max = !empty($this->limit) ? $this->limit : $count;
    for ($k = 0; $k < $max; $k++) {
      foreach ($votes as $vote) {
        if ($vote instanceof OrdinalVote) {
          $candidates = $vote->getCandidates($k);
          if (!empty($candidates)) {
            foreach ($candidates as $candidate) {
              if (!isset($results[$candidate])) {
                $results[$candidate] = 0;
              }
              $results[$candidate] += $vote->getWeight();
            }
          }
        }
      }
      arsort($results);
      if (reset($results) >= $majority) {
        break;
      }
    }
    foreach ($this->getExcludedCandidates() as $candidate) {
      unset($results[$candidate]);
    }
    return $results;
  }
  
}
