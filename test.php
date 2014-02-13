<?php

include "classes/lib.php";

$votes = array();

$a = new OrdinalVote(3);
$a->addRanking('A', 1);
$a->addRanking('B', 2);
$a->addRanking('C', 3);

$votes[] = $a;

$b = new OrdinalVote(1);
$b->addRanking('A', 3);
$b->addRanking('B', 1);
$b->addRanking('C', 2);

$votes[] = $b;

$c = new OrdinalVote(1);
$c->addRanking('A', 2);
$c->addRanking('B', 3);
$c->addRanking('C', 1);

$votes[] = $c;

$d = new OrdinalVote(1);
$d->addRanking('A', 3);
$d->addRanking('B', 2);
$d->addRanking('C', 1);

$votes[] = $d;

$method = new CondorcetInstantRunoff();
var_dump($method->elect($votes));
