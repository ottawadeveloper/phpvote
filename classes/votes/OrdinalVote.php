<?php

class OrdinalVote extends AbstractVote {
  
  private $rankings = array();
  
  public function addRanking($name, $rank) {
    if (!isset($this->rankings[$rank])) {
      $this->rankings[$rank] = array();
    }
    $this->rankings[$rank][] = $name;
  }
  
  public function getRankings() {
    ksort($this->rankings);
    return array_values($this->rankings);
  }
  
  public function getMostPreferred($exclusion = array(), $limit = 0) {
    $choices = $this->getRankings();
    $examined = 0;
    foreach ($choices as $candidates) {
      foreach ($candidates as $candidate) {
        $examined++;
        if (empty($limit) || ($examined <= $limit)) {
          if (!in_array($candidate, $exclusion)) {
            return $candidate;
          }
        }
      }
    }
    return NULL;
  }
  
  public function getCandidates($rank) {
    $choices = $this->getRankings();
    if (isset($choices[$rank])) {
      return $choices[$rank];
    }
    return NULL;
  }
  
  public function getRanking($candidate) {
    $choices = $this->getRankings();
    foreach ($choices as $rank => $candidates) {
      if (in_array($candidate, $candidates)) {
        return $rank;
      }
    }
    return count($choices);
  }
  
  public function preference($x, $y) {
    $rankX = $this->getRanking($x);
    $rankY = $this->getRanking($y);
    return $rankX < $rankY ? 1 : 0;
  }
  
  public function getLeastPreferred($exclusion = array()) {
    $choices = $this->getRankings();
    array_reverse($choices);
    foreach ($choices as $k => $candidates) {
      array_reverse($candidates);
      foreach ($candidates as $candidate) {
        if (!in_array($candidate, $exclusion)) {
          return $candidate;
        }
      }
    }
    return NULL;
  }
  
}