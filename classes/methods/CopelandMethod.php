<?php

class CopelandMethod extends AbstractCondorcetMethod {
    
  public function elect(array $votes, $reset = TRUE) {
    if ($reset) {
      $this->resetExcludedCandidates();
    }
    return $this->calculateCopelandSet($votes);
  }
  
  protected function calculateCopelandSet(array $votes) {
    $pairwise = $this->calculatePairwiseMatrix($votes);
    $wins = array();
    $candidates = $this->getAllCandidates($votes);
    foreach ($candidates as $candidate) {
      $wins[$candidate] = 0;
      foreach ($candidates as $opponent) {
        if ($candidate != $opponent) {
          if ($pairwise[$candidate][$opponent] > $pairwise[$opponent][$candidate]) {
            $wins[$candidate]++;
          }
        }
      }
    }
    arsort($wins);
    return $wins;
  }
  
}