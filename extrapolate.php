<?php

$file = '/var/www/votes/c41ge.csv';

$h = fopen($file, 'r');

if (empty($h)) {
  die();
}

function getProvince($str) {
  $pieces = explode('/', $str);
  return trim($pieces[0]);
} 

function getParty($str) {
  if (strpos($str, 'Liberal/') !== FALSE) {
    return 'Liberal';
  }
  elseif (strpos($str, 'Conservative/') !== FALSE) {
    return 'Conservative';
  }
  elseif (strpos($str, 'NDP-') !== FALSE) {
    return 'NDP';
  }
  elseif (strpos($str, 'Green Party/') !== FALSE) {
    return 'Green';
  }
  elseif (strpos($str, '/Bloc') !== FALSE) {
    return 'Bloc';
  }
  else {
    return 'Other';
  }
  return NULL;
}

$choiceMatrix = array(
  'Conservative' => array(
    'NDP' => 24.7,
    'Liberal' => 16.9,
    'Green' => 9.6,
    'Bloc' => 0.7,
    'Other' => 2.6,
    'none' => 29.8
  ),
  'NDP' => array(
    'Conservative' => 11.8,
    'Liberal' => 37.6,
    'Green' => 20.8,
    'Bloc' => 10.8,
    'Other' => 1.5,
    'none' => 17.4,
  ),
  'Liberal' => array(
    'Conservative' => 11.3,
    'NDP' => 53.7,
    'Green' => 11.6,
    'Bloc' => 4.1,
    'Other' => 1.0,
    'none' => 18.3,
  ),
  'Green' => array(
    'Conservative' => 11.5,
    'NDP' => 41.1,
    'Liberal' => 19.2,
    'Bloc' => 4.2,
    'Other' => 2.8,
    'none' => 21.1,
  ),
  'Bloc' => array(
    'Conservative' => 4.8,
    'NDP' => 49.2,
    'Liberal' => 14.8,
    'Green' => 9.5,
    'Other' => 1.3,
    'none' => 20.3,
  ),
  'Other' => array(
    'Conservative' => 14.9,
    'NDP' => 18.7,
    'Liberal' => 7.1,
    'Green' => 12.7,
    'Bloc' => 2.0,
    'none' => 44.5,
  ),
);

$partyRepresentation = array(
  'Liberal' => 308,
  'Conservative' => 307,
  'NDP' => 308,
  'Other' => 285,
  'Green' => 304,
  'Bloc' => 75,
  'none' => 308,
);

function buildDecisionMatrix(&$results, &$context) {
  $totalVotes = array_sum($context['votes']);
  foreach ($context['parties'] as $party) {
    $subcontext = array_merge($context, array(
      'last' => $party,
      'choices' => array($party),
      'votes' => $context['votes'][$party],
      'totalVotes' => $totalVotes,
    ));
    buildDecisionChain($results, $subcontext);
  }
}

function buildDecisionChain(&$results, &$context) {
  if (count($context['choices']) === count($context['parties'])) {
    $results[] = array(
      'district' => $context['district'],
      'province' => $context['province'],
      'choices' => implode(' > ', $context['choices']),
      'voters' => $context['votes'],
    );
    return;
  }
  $options = array();
  foreach ($context['parties'] as $party) {
    if (!in_array($party, $context['choices'])) {
      // A multiplier is required because the Bloc doesn't exist in most ridings.
      // This should balance out the Bloc's votes in larger ridings (as well as
      // having a minor impact on the Green party).
      $multiplier = 1;
      if (isset($context['partyBalance'][$party])) {
        $multiplier = $context['partyBalanceMax'] / $context['partyBalance'][$party];
      }
      $options[$party] = $context['choiceMatrix'][$context['last']][$party] * $multiplier;
    }
  }
  if (empty($context['forceFullBallot'])) {
    $options['none'] = $context['choiceMatrix'][$context['last']]['none'];
  }
  $total = array_sum($options);
  foreach ($options as $party => $fraction) {
    $multiplier = $fraction / $total;
    $voters = round($context['votes'] * $multiplier);
    if ($voters > 0) {
      if ($party === 'none') {
        $results[] = array(
          'district' => $context['district'],
          'province' => $context['province'],
          'choices' => implode(' > ', $context['choices']),
          'voters' => $voters,
        );
      }
      else {
        $subcontext = $context;
        $subcontext['choices'][] = $party;
        $subcontext['last'] = $party;
        $subcontext['votes'] = $voters;
        buildDecisionChain($results, $subcontext);
      }
    }
  }
}

$skip = 1;
$adjusted = array();
$byParty = array();
$firstRound = array();
while(($line = fgetcsv($h)) !== FALSE) {
  if ($skip) { $skip--; }
  else {
    $prov = getProvince($line[0]);
    $party = getParty($line[3]);
    $district = trim($line[1]);
    
    if (empty($firstRound[$prov])) {
      $firstRound[$prov] = array();
    }
    if (empty($firstRound[$prov][$district])) {
      $firstRound[$prov][$district] = array();
    }
    $firstRound[$prov][$district][$party] = trim($line[6]) * 1;
  }
}
fclose($h);

$votes = array();

foreach ($firstRound as $province => $districts) {
  foreach ($districts as $district => $ballots) {
    $context = array(
      'province' => $province,
      'district' => $district,
      'votes' => $ballots,
      'parties' => array_keys($ballots),
      'choiceMatrix' => $choiceMatrix,
      'partyBalance' => $partyRepresentation,
      'partyBalanceMax' => max($partyRepresentation),
      'forceFullBallot' => FALSE,
    );
    buildDecisionMatrix($votes, $context);
  }
}

echo '<pre>';
echo '"Province","District","Ranked Choices","Votes"' . PHP_EOL;
foreach ($votes as $vote) {
  echo '"' . $vote['province'] . '","' . $vote['district'] . '","' . $vote['choices'] . '","', $vote['voters'] . '"' . PHP_EOL;
}
echo '</pre>';