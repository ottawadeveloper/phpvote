<?php

class Plurality extends GenericElectionMethod {
  
  public function elect(array $votes, $reset = TRUE) {
    if ($reset) {
      $this->resetExcludedCandidates();
    }
    $results = array();
    foreach ($votes as $vote) {
      if ($vote instanceof AbstractVote) {
        $best = $vote->getMostPreferred();
        if (!isset($results[$best])) {
          $results[$best] = 0;
        }
        $results[$best] += $vote->getWeight();
      }
    }
    arsort($results);
    $excluded = $this->getExcludedCandidates();
    foreach ($excluded as $candidate) {
      unset($results[$candidate]);
    }
    return $results;
  }
  
}
