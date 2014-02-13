<?php

class CoombsMethod extends AbstractElectionMethod {
  
  private $excludedCandidates = array();
  
  public function elect(array $votes, $reset = TRUE) {
    if ($reset) {
      $this->resetExcludedCandidates();
    }
    return array(
      $this->runoff($votes) => 1,
    );
  }
  
  private function runoff(array $votes) {
    $threshold = $this->majorityThreshold($votes);
    $results = $this->iterateElection($votes);
    if (empty($results)) {
      return NULL;
    }
    while (count($results)) {
      if (reset($results) >= $threshold) {
        reset($results);
        return key($results);
      }
      else {
        $this->addExcludedCandidate($this->leastPreferred($votes));
        $results = $this->iterateElection($votes);
      }
    }
    return NULL;
  }
  
  private function iterateElection(array $votes) {
    $results = array();
    foreach ($votes as $vote) {
      if ($vote instanceof OrdinalVote) {
        $can = $vote->getMostPreferred($this->getExcludedCandidates());
        if ($can !== NULL) {
          if (!isset($results[$can])) {
            $results[$can] = 0;
          }
          $results[$can] += $vote->getWeight();
        }
      }
    }
    arsort($results);
    return $results;
  }
  
  
  
}