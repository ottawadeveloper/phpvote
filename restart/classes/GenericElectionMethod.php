<?php

define('ELECTION_REQUIRE_STRICT', 1);
define('ELECTION_REQUIRE_WELL_ORDERED', 2);
define('ELECTION_REQUIRE_ALL', 4);
define('ELECTION_REQUIRE_RANKED', 8);
define('ELECTION_REQUIRE_RATED', 16);

define('ELECTION_BALLOT_ERROR', 1);
define('ELECTION_BALLOT_SKIP', 2);

abstract class GenericElectionMethod {
  
  private $requireStrict = FALSE;
  private $requireWellOrdered = FALSE;
  private $requireAllCandidates = FALSE;
  private $minRequiredCandidates = NULL;
  private $maxRequiredCandidates = NULL;
  
  private $requireRanked = FALSE;
  private $requireRated = FALSE;
  
  private $allCandidates = array();
  
  private $votes = array();
  
  private $messages = array();
  
  private $ballotErrorHandling = ELECTION_BALLOT_ERROR;
  
  public function __construct($ballotErrorMethod = ELECTION_BALLOT_ERROR, $flags = 0, $min = NULL, $max = NULL) {
    $this->ballotErrorMethod = $ballotErrorMethod;
    $this->minRequiredCandidates = $min;
    $this->maxRequiredCandidates = $max;
    $this->requireStrict = $flags & ELECTION_REQUIRE_STRICT;
    $this->requireWellOrdered = $flags & ELECTION_REQUIRE_WELL_ORDERED;
    $this->requireAllCandidates = $flags & ELECTION_REQUIRE_ALL;
    $this->requireRanked = $flags & ELECTION_REQUIRE_RANKED;
    if (!$this->requireRanked) {
      $this->requireRated = $flags & ELECTION_REQUIRE_RATED;
    }
  }
  
  protected function addMessage($message) {
    $this->messages[] = $message;
  }

  public function initialize(array $votes) {
    $this->votes = $votes;
    $this->allCandidates = array();
  }
  
  public abstract function runElection();
  
  public function getAllCandidates() {
    if (empty($this->allCandidates)) {
      foreach ($this->votes as $vote) {
        if ($vote instanceof AbstractBallot) {
          foreach ($vote->getAllCandidates() as $candidate) {
            if (!in_array($candidate, $this->allCandidates)) {
              $this->allCandidates[] = $candidate;
            }
          }
        }
      }
    }
    return $this->allCandidates;
  }
  
  public function getVotes() {
    return $this->votes;
  }
  
  public function checkRequirements() {
    foreach ($this->getVotes() as $vote) {
      if ($vote instanceof AbstractBallot) {
        if ($this->requireRanked && (!$vote instanceof RankedBallot)) {
          $this->addMessage('A ballot is not of type RankedBallot, for a method that requires it.');
          if ($this->ballotErrorHandling === ELECTION_BALLOT_ERROR) { return FALSE; }
        }
        if ($this->requireRated && (!$vote instanceof RatedBallot)) {
          $this->addMessage('A ballot is not of type RatedBallot, for a method that requires it.');
          if ($this->ballotErrorHandling === ELECTION_BALLOT_ERROR) { return FALSE; }
        }
        if ($this->requireStrict && (!$vote->isStrict())) {
          $this->addMessage('A ballot is not strictly ordered, for a method that requires it.');
          if ($this->ballotErrorHandling === ELECTION_BALLOT_ERROR) { return FALSE; }
        }
        if ($this->requireWellOrdered && (!$vote->isWellOrdered())) {
          $this->addMessage('A ballot is not well-ordered, for a method that requires it.');
          if ($this->ballotErrorHandling === ELECTION_BALLOT_ERROR) { return FALSE; }
        }
        if ($this->requireAllCandidates) {
          $candidates = $this->getAllCandidates();
          if (!$vote->isComplete($candidates)) {
            $this->addMessage('A ballot has not ranked all the candidates, for a method that requires it.');
            if ($this->ballotErrorHandling === ELECTION_BALLOT_ERROR) { return FALSE; }
          }
        }
      }
      else {
        $this->addMessage('A ballot is not of type AbstractBallot!');
        if ($this->ballotErrorHandling === ELECTION_BALLOT_ERROR) { return FALSE; }
      }
    }
    return TRUE;
  }
  
  public function getWinner() {
    $winners = $this->getWinnerPool();
    if (count($winners) === 1) {
      return reset($winners);
    }
    return NULL;
  }
  
  public abstract function getWinnerPool();
  
}