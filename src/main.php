<?php

require_once __DIR__ . '/../vendor/autoload.php';

use SAT\BruteSolver;
use SAT\UnitPropagationSolver;
use SAT\Parser;

$files = collect([
    '/../data/php/php-1-2.cnf',
    '/../data/php/php-2-3.cnf',
    '/../data/php/php-3-4.cnf',
    '/../data/php/php-4-5.cnf',
    '/../data/php/php-5-6.cnf',
    '/../data/php/php-6-7.cnf',
    '/../data/php/php-7-8.cnf',
    '/../data/php/php-8-9.cnf',
    '/../data/php/php-9-10.cnf',
    '/../data/php/php-10-11.cnf',
    '/../data/php/php-11-12.cnf',
    '/../data/php/php-12-13.cnf',
    '/../data/php/php-13-14.cnf',
    '/../data/php/php-14-15.cnf',
    '/../data/php/php-15-16.cnf',
    '/../data/kcnf/kcnf-10-45.cnf',
    '/../data/kcnf/kcnf-20-55.cnf',
    '/../data/kcnf/kcnf-30-65.cnf',
    '/../data/kcnf/kcnf-40-75.cnf',
    '/../data/kcnf/kcnf-50-85.cnf',
    '/../data/kcnf/kcnf-60-95.cnf',
]);

$files->each(function ($file) {
    $parser = new Parser();
    $CNF = $parser->load( __DIR__ . $file);
    $solver = new UnitPropagationSolver($CNF);
    $start_time = microtime(true);
    $solution = $solver->solve();
    $end_time = microtime(true);
    $execution_time = number_format($end_time - $start_time, 2);
    echo 'UnitPropagationSolver,' . $file . ',' . $execution_time . PHP_EOL;
});

$files = collect([
    '/../data/php/php-1-2.cnf',
    '/../data/php/php-2-3.cnf',
    '/../data/php/php-3-4.cnf',
    '/../data/php/php-4-5.cnf',
    '/../data/php/php-5-6.cnf',
    '/../data/kcnf/kcnf-10-45.cnf',
    '/../data/kcnf/kcnf-20-55.cnf',
    '/../data/kcnf/kcnf-30-65.cnf',
    '/../data/kcnf/kcnf-40-75.cnf',
]);

$files->each(function ($file) {
    $parser = new Parser();
    $CNF = $parser->load( __DIR__ . $file);
    $solver = new BruteSolver($CNF);
    $start_time = microtime(true);
    $solution = $solver->solve();
    $end_time = microtime(true);
    $execution_time = number_format($end_time - $start_time, 2);
    echo 'BruteSolver,' . $file . ',' . $execution_time . PHP_EOL;
});
