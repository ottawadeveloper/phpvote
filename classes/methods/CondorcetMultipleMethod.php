<?php



class CondorcetMultipleMethod extends AbstractCondorcetMethod {
  
  /**
   *
   * @var AbstractElectionMethod
   */
  private $fallback = NULL;
  private $set = CONDORCET_SCHWARTZ_SET;
  
  public function __construct(AbstractElectionMethod $method, $set = CONDORCET_SCHWARTZ_SET) {
    if ($method instanceof AbstractCondorcetMethod) {
      throw new Exception('You really should not nest Condorcet methods...');
    }
    $fallback = $method;
    $this->set = $set;
  }
  
  public function elect(array $votes, $reset = TRUE) {
    $winner = $this->calculateCondorcetWinner($votes);
    if (!empty($winner)) {
      return array(
        $winner => 1,
      );
    }
    else {
      $set = $this->getSet($votes);
      if (count($set) == 1) {
        return array(
          reset($set) => 1,
        );
      }
      $this->fallback->resetExcludedCandidates();
      $candidates = $this->getAllCandidates($votes);
      foreach ($candidates as $candidate) {
        if (!in_array($candidate, $set)) {
          $this->fallback->addExcludedCandidate($candidate);
        }
      }
      return $this->fallback->elect($votes, FALSE);
    }
  }
  
  private function getSet($votes) {
    switch ($this->set) {
      case CONDORCET_COPELAND_SET:
        return $this->calculateCopelandSet($votes);
      case CONDORCET_SMITH_SET:
        return $this->calculateSmithSet($votes);
      default:
        return $this->calculateSchwartzSet($votes);
    }
  }
  
}
