<?php

class RankedBallot extends AbstractBallot {
  
  private $ranking = array();
  private $max = 0;
  
  private $rankedCandidates = NULL;
  
  public function addCandidate($candidate, $rank = NULL) {
    if ($rank === NULL) {
      $rank = $this->max + 1;
    }
    if (!isset($this->ranking[$rank])) {
      $this->ranking[$rank] = array();
    }
    $this->ranking[$rank][] = $candidate;
    if ($rank > $this->max) {
      $this->max = $rank;
    }
    $this->resetAll();
  }
  
  protected function resetAll() {
    parent::resetAll();
    $this->rankedCandidates = NULL;
  }
  
  protected function getOrderedCandidates() {
    if (empty($this->rankedCandidates)) {
      $temp = $this->ranking;
      ksort($temp);
      $this->rankedCandidates = array_values($temp);
    }
    return $this->rankedCandidates;
  }
  
}
