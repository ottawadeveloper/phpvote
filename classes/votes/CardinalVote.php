<?php

class CardinalVote extends AbstractVote {
  
  private $preferences = array();
  
  public function addScore($name, $score) {
    $this->preferences[$name] = $score;
  }
  
  public function getScore() {
    return $this->preferences;
  }
  
  public function getMostPreferred($exclusions = array()) {
    $max = NULL;
    foreach ($this->preferences as $name => $value) {
      if (!in_array($name, $exclusions)) {
        if (empty($max)) {
          $max = $name;
        }
        else {
          if ($value > $this->preferences[$max]) {
            $max = $name;
          }
        }
      }
    }
    return $max;
  }
  
}