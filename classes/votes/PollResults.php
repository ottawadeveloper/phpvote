<?php

class PollResults {
  
  private $votes = array();
  
  private $name = '';
  
  public function __construct($pollname) {
    $this->name = $pollname;
  }
  
  public function addVote(AbstractVote $vote) {
    $this->votes[] = $vote;
  }
  
  public function getTotalCount() {
    $total = 0;
    foreach ($this->votes as $vote) {
      if ($vote instanceof AbstractVote) {
        $total += $vote->getWeight();
      }
    }
    return $total;
  }
  
  public function getVotes() {
    return $this->votes;
  }
  
  
  
}