<?php

define('CONDORCET_SMITH_SET', 'smith');
define('CONDORCET_SCHWARTZ_SET', 'schwartz');

abstract class AbstractCondorcetMethod extends GenericElectionMethod {
  
  protected function calculateSchwartzSet(array $votes) {
    $pairings = $this->calculatePairwiseMatrix($votes);
    $candidates = $this->getAllCandidates($votes);
    $relation = array();
    foreach ($candidates as $x) {
      $relation[$x] = array();
      foreach ($candidates as $y) {
        if ($x !== $y) {
          $relation[$x][$y] = $pairings[$x][$y] > $pairings[$y][$x];
        }
      }
    }
    return $this->kosarajuMaximalSet($relation, $candidates);
  }
  
  protected function calculateSmithSet(array $votes) {
    $pairings = $this->calculatePairwiseMatrix($votes);
    $candidates = $this->getAllCandidates($votes);
    $relation = array();
    foreach ($candidates as $x) {
      $relation[$x] = array();
      foreach ($candidates as $y) {
        if ($x !== $y) {
          $relation[$x][$y] = $pairings[$x][$y] >= $pairings[$y][$x];
        }
      }
    }
    return $this->kosarajuMaximalSet($relation, $candidates);
  }
  
  protected function kosarajuMaximalSet(array $originalRelation, $candidates) {
    $maximalSet = array();
    $searchOrder = array();
    $k = 1;
    $candidateMapping = array();
    foreach ($candidates as $i) {
      $candidateMapping[$k] = $i;
      $searchOrder[$k] = $k;
      $k++;
    }
    $relation = array();
    foreach ($candidateMapping as $new => $old) {
      $relation[$new] = array();
      foreach ($candidateMapping as $newj => $oldj) {
        if ($new != $newj) {
          $relation[$new][$newj] = $originalRelation[$old][$oldj];
        }
      }
    }
    $context = $this->depthFirstSearch($relation, $searchOrder);
    
    $nextSearchOrder = array();
    for ($k = 1; $k <= count($candidates); $k++) {
      $nextSearchOrder[$k] = $context['finishOrder'][count($candidates) + 1 - $k];
    }
    $transposedRelation = array();
    foreach ($candidateMapping as $new => $old) {
      $transposedRelation[$k] = array();
      foreach ($candidateMapping as $newj => $oldj) {
        if ($new != $newj) {
          $transposedRelation[$new][$newj] = $relation[$newj][$new];
        }
      }
    }
    
    $context = $this->depthFirstSearch($transposedRelation, $nextSearchOrder);
    foreach ($candidateMapping as $num => $candidate) {
      if (!$context['treeConnects'][$num]) {
        $maximalSet[] = $candidate;
      }
    }
    return $maximalSet;
  }
  
  protected function depthFirstSearch($relation, $searchOrder) {
    $context = array(
      'finishOrder' => array(),
      'finishOrderCount' => 0,
      'visited' => array(),
      'tree' => array(),
      'treeConnects' => array(),
      'treeCount' => 0,
    );
    foreach ($searchOrder as $index) {
      $context['visited'][$index] = FALSE;
      $context['treeConnects'][$index] = FALSE;
    }
    foreach ($searchOrder as $index => $rootIndex) {
      if (!$context['visited'][$rootIndex]) {
        $context['treeCount']++;
        $this->visitNode($relation, $searchOrder, $rootIndex, $context);
      }
    }
    return $context;
  }
  
  protected function visitNode($relation, $order, $visitIndex, &$context) {
    $context['tree'][$visitIndex] = $context['treeCount'];
    $context['visited'][$visitIndex] = TRUE;
    foreach ($order as $index => $probeIndex) {
      if (!empty($relation[$visitIndex][$probeIndex])) {
        if (empty($context['visited'][$probeIndex])) {
          $this->visitNode($relation, $order, $probeIndex, $context);
        }
        else {
          if ($context['tree'][$probeIndex] < $context['treeCount']) {
            $context['treeConnects'][$context['treeCount']] = TRUE;
          }
        }
      }
    }
    $context['finishOrderCount']++;
    $context['finishOrder'][$context['finishOrderCount']] = $visitIndex;
  }
  
  protected function calculateCondorcetWinner(array $votes) {
    $pairings = $this->calculatePairwiseMatrix($votes);
    $candidates = $this->getAllCandidates($votes);
    $toWin = count($candidates) - 1;
    foreach ($candidates as $x) {
      $wins = 0;
      foreach ($candidates as $y) {
        if ($x != $y) {
          if ($pairings[$x][$y] > $pairings[$y][$x]) {
            $wins++;
          }
        }
      }
      if ($wins === $toWin) {
        return $x;
      }
    }
    return NULL;
  }
  
  protected function calculatePairwiseMatrix(array $votes) {
    $pairs = array();
    $candidates = $this->getAllCandidates($votes);
    foreach ($candidates as $x) {
      $pairs[$x] = array();
      foreach ($candidates as $y) {
        if ($x != $y) {
          $pairs[$x][$y] = $this->calculatePairwiseEntry($votes, $x, $y);
        }
      }
    }
    return $pairs;
  }
  
  protected function calculatePairwiseEntry(array $votes, $x, $y) {
    $total = 0;
    foreach ($votes as $vote) {
      if ($vote instanceof OrdinalVote) {
        if ($vote->preference($x, $y)) {
          $total += $vote->getWeight();
        }
      }
    }
    return $total;
  }
  
  
}