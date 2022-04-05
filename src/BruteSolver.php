<?php

namespace SAT;

class BruteSolver implements SolverInterface
{
    public function __construct(CNF $CNF)
    {
        $this->CNF = $CNF;
        $this->variables = collect();
        $this->clauses = collect();
    }

    public function next($variables)
    {
        for ($index = count($variables) - 1; $index > 0; $index--) {
            if ($variables[$index] > 0) {
                $variables[$index] = -$variables[$index];
                return $variables;
            } else {
                $variables[$index] = -$variables[$index];
            }
        }
        if ($variables[$index] > 0) {
            $variables[$index] = -$variables[$index];
            return $variables;
        } else {
            return null;
        }
    }

    public function solve()
    {
        $variables = $this->CNF->variables()->toArray();

        do {
            $solved = $this->CNF->clauses()
                ->every(function ($clause) use ($variables) {
                    return $clause
                        ->contains(function ($literal) use ($variables) {
                            return $literal === $variables[abs($literal) - 1];
                        });
                });
            if ($solved) {
                $solution = $variables;
                break;
            }
        } while ($variables = $this->next($variables));

        return $solution;
    }
}
