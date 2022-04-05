<?php

require_once __DIR__ . '/../vendor/autoload.php';

use SAT\BruteSolver;
use SAT\Parser;

$parser = new Parser();

$CNF = $parser->load( __DIR__ . '/../data/hole6.cnf');

$solver = new BruteSolver($CNF);

$solution = $solver->solve();

print_r($solution);
