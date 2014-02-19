<?php

define('PROPORTIONAL_QUOTA_HARE', 'hare');
define('PROPORTIONAL_QUOTA_DROOP', 'droop');
define('PROPORTIONAL_QUOTA_HAGENBACH', 'hagenbach-bischoff');
define('PROPORTIONAL_QUOTA_IMPERIALI', 'imperiali');

define('PROPORTIONAL_QUOTIENT_DHONDT', 'dhondt');
define('PROPORTIONAL_QUOTIENT_SAINTE_LAGUE', 'sanite-lague');

class ProportionalContest implements ElectoralSystemInterface {
  
  private $quotaMethod = PROPORTIONAL_QUOTA_HARE;
  private $minThreshold = 0;
  
  public function __construct($quotaMethod = PROPORTIONAL_QUOTA_HARE) {
    $this->quotaMethod = $quotaMethod;
  }
  
  private function getQuota($seats, $votes) {
    switch ($this->quotaMethod) {
      case PROPORTIONAL_QUOTA_IMPERIALI:
        return floor($votes / ($seats + 2));
      case PROPORTIONAL_QUOTA_HAGENBACH:
        return floor($votes / ($seats + 1));
      case PROPORTIONAL_QUOTA_DROOP:
        return 1 + $votes / ($seats + 1);
      case PROPORTIONAL_QUOTA_HARE:
      default:
        return floor($votes / $seats);
    }
  }
  
  public function election(array $ridingResults) {
    $seats = count($ridingResults);
    $votes = 0;
    $parties = array();
    foreach ($ridingResults as $result) {
      if ($result instanceof RidingResults) {
        foreach ($result->getAllVotes() as $vote) {
          if ($vote instanceof AbstractVote) {
            if (!isset($parties[$vote->getMostPreferred()])) {
              $parties[$vote->getMostPreferred()] = 0;
            }
            $parties[$vote->getMostPreferred()] += $vote->getWeight();
            $votes += $vote->getWeight();
          }
        }
      }
    }
    $min = floor($votes * $this->minThreshold);
    foreach ($parties as $party => $pvotes) {
      if ($pvotes < $min) {
        unset($parties[$party]);
      }
    }
    $votes = array_sum($parties);
    $quota = $this->getQuota($seats, $votes);
    $results = array();
    $remainders = array();
    $seatsLeft = $seats;
    foreach ($parties as $party => $pvotes) {
      $seatsWon = floor($pvotes / $quota);
      $results[$party] = $seatsWon;
      $seatsLeft -= $seatsWon;
      $remainders[$party] = $pvotes - ($seatsWon * $quota);
    }
    arsort($remainders);
    $list = array_keys($remainders);
    $pos = 0;
    while ($seatsLeft > 0) {
      $results[$list[$pos]]++;
      $pos++;
      if ($pos >= count($list)) {
        $pos -= count($list);
      }
      $seatsLeft--;
    }
    arsort($results);
    return $results;
  }
  
}
