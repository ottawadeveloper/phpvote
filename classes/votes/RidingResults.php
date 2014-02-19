<?php

class RidingResults {
  
  private $ridingName = '';
  private $category = NULL;
  
  private $polls = array();
  
  public function __construct($name, $category = NULL) {
    $this->ridingName = $name;
    $this->category = $category;
  }
  
  public function addPollResult(PollResults $pr) {
    $this->polls[] = $pr;
  }
  
  public function getAllVotes() {
    $votes = array();
    foreach ($this->polls as $poll) {
      if ($poll instanceof PollResults) {
        $votes = array_merge($votes, $poll->getVotes());
      }
    }
    return $votes;
  }
  
  public function totalVotes() {
    $total = 0;
    foreach ($this->polls as $poll) {
      if ($poll instanceof PollResults) {
        $total += $poll->getTotalCount();
      }
    }
    return $total;
  }
  
  public function getPolls() {
    return $this->polls;
  }
  
}