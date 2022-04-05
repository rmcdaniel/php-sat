<?php

namespace SAT;

interface SolverInterface {
    public function __construct(CNF $CNF);
    public function solve();
}
