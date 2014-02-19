<?php

class PluralityElection extends GenericElectionMethod implements OrderedElectionMethod {
  
  private $results = array();
  
  public function __construct($errorMethod = ELECTION_BALLOT_ERROR) {
    parent::__construct($errorMethod, ELECTION_REQUIRE_STRICT, 1);
  }
  
  public function runElection() {
    foreach ($this->getVotes() as $vote) {
      
    }
  }
  
  public function getWinnerPool() {
    
  }
  
  public function getElectionResults() {
    
  }
  
}
