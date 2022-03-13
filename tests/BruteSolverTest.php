<?php

use PHPUnit\Framework\TestCase;
use SAT\CNF;
use SAT\Parser;
use SAT\BruteSolver;

class BruteSolverTest extends TestCase
{
    public function testSolver()
    {
        $solver = new BruteSolver(new CNF([
            [1, -3],
            [2, 3, -1],
        ]));

        $solution = $solver->solve();

        $this->assertTrue(in_array($solution, [
            [1, 2, 3],
            [1, 2, -3],
            [1, -2, 3],
            [-1, 2, -3],
            [-1, -2, -3],
        ]));
    }

    public function testSolverTutorial()
    {
        $solver = new BruteSolver(new CNF([
            [1, 2, 3, 4, 5],
            [-1, -2, -3, -4, -5],
            [1, -2, 3, -4, 5],
            [5],
            [-1, 2],
        ]));

        $solution = $solver->solve();

        $this->assertTrue(in_array($solution, [
            [1, 2, 3, -4, 5],
            [1, 2, -3, 4, 5],
            [1, 2, -3, -4, 5],
            [-1, 2, 3, 4, 5],
            [-1, 2, 3, -4, 5],
            [-1, 2, -3, 4, 5],
            [-1, 2, -3, -4, 5],
            [-1, -2, 3, 4, 5],
            [-1, -2, 3, -4, 5],
            [-1, -2, -3, 4, 5],
            [-1, -2, -3, -4, 5],
        ]));
    }
}
