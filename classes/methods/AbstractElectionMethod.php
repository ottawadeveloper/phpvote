<?php

abstract class AbstractElectionMethod implements ElectionMethodInterface {
  
  private $excludedCandidates = array();
  
  protected function addExcludedCandidate($name) {
    $this->excludedCandidates[] = $name;
  }
  
  protected function getExcludedCandidates() {
    return $this->excludedCandidates;
  }
  
  protected function resetExcludedCandidates() {
    $this->excludedCandidates = array();
  }
  
  protected function getNumberOfCandidates(array $votes) {
    $max = 0;
    foreach ($votes as $vote) {
      $rankings = $vote->getRankings();
      if (count($rankings) > $max) {
        $max = count($rankings);
      }
    }
    return $max;
  }
  
  protected function getAllCandidates(array $votes) {
    $allCandidates = array();
    foreach ($votes as $vote) {
      if ($vote instanceof OrdinalVote) {
        $list = $vote->getRankings();
        foreach ($list as $rank => $candidates) {
          foreach ($candidates as $candidate) {
            $allCandidates[$candidate] = $candidate;
          }
        }
      }
    }
    return array_values($allCandidates);
  }
  
  protected function leastPreferred(array $votes) {
    $results = array();
    foreach ($votes as $vote) {
      if ($vote instanceof OrdinalVote) {
        $least = $vote->getLeastPreferred($this->excludedCandidates);
        if (!isset($results[$least])) {
          $results[$least] = 0;
        }
        $results[$least] += $vote->getWeight();
      }
    }
    arsort($results);
    return reset(array_keys($results));
  }
  
  protected function majorityThreshold(array $votes) {
    $total = $this->totalVotes($votes);
    $required = floor($total / 2);
    if ($required < $total / 2) {
      $required++;
    }
    return $required;
  }
  
  protected function totalVotes(array $votes) {
    $total = 0;
    foreach ($votes as $vote) {
      $total += $vote->getWeight();
    }
    return $total;
  }
  
}