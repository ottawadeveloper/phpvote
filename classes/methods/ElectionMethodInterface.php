<?php

interface ElectionMethodInterface {
  
  function elect(array $votes, $reset = TRUE);
  
}