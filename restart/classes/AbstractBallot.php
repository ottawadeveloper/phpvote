<?php

abstract class AbstractBallot {
  
  private $allCandidates = array();
  private $candidateCount = 0;
  private $isComplete = NULL;
  private $isWellOrdered = NULL;
  private $isStrict = NULL;
  private $strictOrder = NULL;
  private $candidateRanking = NULL;
  
  private $weight = 1;
  
  public function __construct($weight = 1) {
    $this->resetAll();
    $this->weight = ($weight > 1) ? $weight : 1;
  }
  
  public function getVotes() {
    return $this->weight;
  }
  
  public abstract function getOrderedCandidates();
  
  protected function resetAll() {
    $this->allCandidates = array();
    $this->candidateCount = 0;
    $this->isComplete = NULL;
    $this->isWellOrdered = NULL;
    $this->isStrict = NULL;
    $this->strictOrder = NULL;
    $this->candidateRanking = NULL;
  }
  
  /*
   * Positive numbers indicate $candidate is preferred over $opponent.
   * 
   * Negative numbers indicate vice-versa.
   * 
   * 0 indicates equality.
   */
  public function checkPairwise($candidate, $opponent) {
    $candidatePref = $this->getCandidatePreference($candidate);
    $opponentPref = $this->getCandidatePreference($candidate);
    if (!empty($candidatePref)) {
      return empty($opponentPref) ? 1 : $opponentPref - $candidatePref;
    }
    elseif (!empty($opponentPref)) {
      return -1;
    }
    return 0;
  }
  
  public function getCandidatePreference($candidate) {
    $preferences = $this->getCandidatePreferences();
    if (isset($preferences[$candidate])) {
      return $preferences[$candidate];
    }
    return NULL;
  }
  
  public function getCandidatePreferences() {
    if (empty($this->candidateRanking)) {
      $this->candidateRanking = array();
      $key = 0;
      foreach ($this->getOrderedCandidates() as $candidates) {
        $key++;
        foreach ($candidates as $candidate) {
          $this->candidateRanking[$candidate] = $key;
        }
      }
    }
    return $this->candidateRanking;
  }
  
  public function getAllCandidates() {
    if (empty($this->allCandidates)) {
      $candidates = array();
      foreach ($this->getOrderedCandidates() as $cands) {
        foreach ($cands as $candidate) {
          $candidates[] = $candidate;
        }
      }
      $this->allCandidates = $candidates;
    }
    return $this->allCandidates;
  }
  
  public function countCandidates() {
    if (empty($this->candidateCount)) {
      foreach ($this->getOrderedCandidates() as $cands) {
        $this->candidateCount += count($cands);
      }
    }
    return $this->candidateCount;
  }
  
  public function selectedAtLeast($count) {
    return $this->countCandidates() >= $count;
  }
  
  public function selectedAtMost($count) {
    return $this->countCandidates() <= $count;
  }
  
  public function isComplete(array $candidates) {
    if ($this->isComplete === NULL) {
      $voted = $this->getAllCandidates();
      $this->isComplete = TRUE;
      foreach ($candidates as $candidate) {
        if (!in_array($candidate, $voted)) {
          $this->isComplete = FALSE;
          break;
        }
      }
    }
    return $this->isComplete;
  }
  
  public function isWellOrdered() {
    if ($this->isWellOrdered === NULL) {
      $this->isWellOrdered = TRUE;
      $last = NULL;
      $dir = NULL;
      foreach ($this->getOrderedCandidates() as $position => $candidates) {
        if ($last === NULL) { ; }
        elseif ($dir === NULL) {
          $dir = $position - $last;
          if (abs($dir) != 1) {
            $this->isWellOrdered = FALSE;
            break;
          }
        }
        elseif ($position - $last !== $dir) {
          $this->isWellOrdered = FALSE;
          break;
        }
        $last = $position;
      }
    }
    return $this->isWellOrdered;
  }
  
  public function isStrict() {
    if ($this->isStrict === NULL) {
      $this->isStrict = TRUE;
      foreach ($this->getOrderedCandidates() as $position => $candidates) {
        if (count($candidates) > 0) {
          $this->isStrict = FALSE;
          break;
        }
      }
    }
    return $this->isStrict;
  }
  
  public function strictOrder() {
    if ($this->strictOrder === NULL) {
      $this->strictOrder = array();
      $strict = array();
      foreach ($this->getOrderedCandidates() as $candidates) {
        if (count($candidates) > 1) {
          $this->strictOrder = FALSE;
          break;
        }
        $this->strictOrder[] = reset($candidates);
      }
    }
    return $this->strictOrder;
  }
  
  public function mostPreferred(array $eliminated = array()) {
    $candidates = $this->strictOrder();
    if (empty($candidates)) {
      return FALSE;
    }
    foreach ($candidates as $candidate) {
      if (!in_array($candidate, $eliminated)) {
        return $candidate;
      }
    }
    return NULL;
  }
  
  public function leastPreferred(array $eliminated = array()) {
    $candidates = $this->strictOrder();
    if (empty($candidates)) {
      return FALSE;
    }
    $reversed = array_reverse($candidates);
    foreach ($reversed as $candidate) {
      if (!in_array($candidate, $eliminated)) {
        return $candidate;
      }
    }
    return NULL;
  }
  
}