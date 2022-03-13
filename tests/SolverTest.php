<?php

use PHPUnit\Framework\TestCase;
use SAT\CNF;
use SAT\Parser;
use SAT\Solver;

class SolverTest extends TestCase
{
    public function testSolver()
    {
        // $solver = new Solver(new CNF([
        //     [1, -3],
        //     [2, 3, -1],
        // ]));

        // $solution = $solver->solve();

        // $this->assertTrue(in_array($solution, [
        //     [1, 2, 3],
        //     [1, 2, -3],
        //     [1, -2, 3],
        //     [-1, 2, -3],
        //     [-1, -2, -3],
        // ]));
    }

    public function testSolverTutorial()
    {
        // echo PHP_EOL;
        // echo CNF::toFormula([
        //     [1, 2, 3, 4, 5],
        //     [-1, -2, -3, -4, -5]
        // ]);
        // echo PHP_EOL;

        // $solver = new Solver(new CNF([
        //     [1, 2, 3, 4, 5],
        //     [-1, -2, -3, -4, -5],
        //     [1, -2, 3, -4, 5],
        //     [5],
        //     [-1, 2],
        // ]));

        // $solver = \Mockery::mock(Solver::class.'[getRandomLiteral,getRandomVariable]', [new CNF([
        //     [1, 2, 3, 4, 5],
        //     [-1, -2, -3, -4, -5],
        //     [1, -2, 3, -4, 5],
        //     [5],
        //     [-1, 2],
        // ])], function ($mock) {
        //     $mock->shouldReceive('getRandomLiteral')->andReturn(2, 1, 5, 4, 4, 5, 2, 1, 2, null);
        //     $mock->shouldReceive('getRandomVariable')->andReturn(1, 3, 4, null);
        // });

        // $solution = $solver->solve();

        // $this->assertTrue(in_array($solution, [
        //     [1, 2, 3, -4, 5],
        //     [1, 2, -3, 4, 5],
        //     [1, 2, -3, -4, 5],
        //     [-1, 2, 3, 4, 5],
        //     [-1, 2, 3, -4, 5],
        //     [-1, 2, -3, 4, 5],
        //     [-1, 2, -3, -4, 5],
        //     [-1, -2, 3, 4, 5],
        //     [-1, -2, 3, -4, 5],
        //     [-1, -2, -3, 4, 5],
        //     [-1, -2, -3, -4, 5],
        // ]));
    }
}
