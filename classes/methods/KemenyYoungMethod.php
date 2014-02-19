<?php

class KemenyYoungMethod extends AbstractCondorcetMethod {
  
  public function elect(array $votes, $reset = TRUE) {
    if ($reset) {
      $this->resetExcludedCandidates();
    }
    $cwinner = $this->calculateCondorcetWinner($votes);
    if (!empty($cwinner)) {
      return array(
        $cwinner => 1,
      );
    }
    $pairwise = $this->calculatePairwiseMatrix($votes);
    $rankings = $this->generateRankings($votes);
    $scores = array();
    foreach ($rankings as $key => $ranking) {
      $scores[$key] = $this->generateRankingScore($ranking, $pairwise);
    }
    arsort($scores);
    $best = max($scores);
    $winners = array();
    foreach ($scores as $key => $points) {
      if ($points === $best) {
        $winners[] = $rankings[$key];
      }
    }
    if (count($winners) === 1) {
      $winningSet = reset($winners);
      $return = array();
      foreach ($winningSet as $rank => $winner) {
        $return[$winner] = count($winningSet) - $rank;
      }
      return $return;
    }
    else {
      $results = array();
      $used = array();
      $exemplar = reset($winners);
      foreach (array_keys($exemplar) as $key) {
        foreach ($winners as $key => $ranking) {
          if (!in_array($ranking[$key], $used)) {
            $used[] = $ranking[$key];
            $results[$ranking[$key]] = count($exemplar) - $key;
          }
        }
      }
      return $results;
    }
  }
  
  protected function generateRankingScore(array $ranking, array &$pairwise) {
    $score = 0;
    for ($k = 0; $k < count($ranking) - 1; $k++) {
      for ($j = $k + 1; $j < count($ranking); $j++) {
        $score += $pairwise[$ranking[$k]][$ranking[$j]];
      }
    }
    return $score;
  }
  
  protected function generateRankings(array $votes) {
    $rankings = array();
    $candidates = $this->getAllCandidates($votes);
    return $this->generateSubRankings($candidates, array());
  }
  
  protected function generateSubRankings(array $candidates, array $start = array()) {
    $rankings = array();
    foreach ($candidates as $candidate) {
      if (!in_array($candidate, $start)) {
        $choice = $start;
        $choice[] = $candidate;
        if (count($choice) !== count($candidates)) {
          $rankings = array_merge($rankings, $this->generateSubRankings($candidates, $choice));
        }
        else {
          $rankings[] = $choice;
        }
      }
    }
    return $rankings;
  }
}
