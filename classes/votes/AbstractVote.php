<?php

abstract class AbstractVote {
  
  private $weight = 1;
  
  public function __construct($weight = 1) {
    if ($weight < 1) {
      $weight = 1;
    }
    $this->weight = $weight;
  }
  
  abstract function getMostPreferred($exclusions = array());
  
  public function getWeight() {
    return $this->weight;
  }
  
  
  
}