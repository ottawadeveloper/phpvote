<?php

class InstantRunoff extends GenericElectionMethod {
  
  private $tworound = FALSE;
  private $limitedOptions = 0;
  
  public function __construct($tworound = FALSE, $limitOptions = 0) {
    $this->tworound = $tworound;
    $this->limitedOptions = $limitOptions;
  }
  
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
    $loop = TRUE;
    while ($loop) {
      $last = count($results);
      if (reset($results) >= $threshold) {
        $loop = FALSE;
      }
      else {
        if ($this->tworound) {
          $skip = 2;
          foreach ($results as $candidate => $v) {
            if ($skip) {
              $skip--;
            }
            else {
              $this->addExcludedCandidate($candidate);
            }
          }
        }
        else {
          end($results);
          $this->addExcludedCandidate(key($results));
        }
        $results = $this->iterateElection($votes);
        if (count($results) == $last) {
          $loop = FALSE;
        }
        if (count($results) < 2) {
          $loop = FALSE;
        }
      }
    }
    reset($results);
    return key($results);
  }
  
  private function iterateElection(array $votes) {
    $results = array();
    foreach ($votes as $vote) {
      if ($vote instanceof OrdinalVote) {
        $can = $vote->getMostPreferred($this->getExcludedCandidates(), $this->limitedOptions);
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