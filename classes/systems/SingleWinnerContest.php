<?php

class SingleWinnerContest implements ElectoralSystemInterface {

  /**
   *
   * @var ElectionMethodInterface
   */
  private $electionMethod = NULL;
  
  public function __construct(ElectionMethodInterface $method) {
    $this->electionMethod = $method;
  }
  
  public function election(array $ridingResults) {
    $results = array();
    foreach ($ridingResults as $rr) {
      if ($rr instanceof RidingResults) {
        $elected = $this->electionMethod->elect($rr->getAllVotes());
        $best = max($elected);
        $winners = array();
        foreach ($elected as $name => $score) {
          if ($score === $best) {
            $winners[] = $name;
          }
        }
        if (count($winners) === 1) {
          $winner = reset($winners);
          if (!isset($results[$winner])) {
            $results[$winner] = 0;
          }
          $results[$winner]++;
        }
        else {
          echo 'TIED: ' . implode(' / ' , $winners);
        }
      }
    }
    arsort($results);
    return $results;
  }
  
}