<?php

namespace SAT;

class UnitPropagationSolver implements SolverInterface
{
    public $CNF;
    public $variables;
    public $clauses;
    public $trail;
    public $decisionLevel;
    public $satisfiable;

    public function __construct(CNF $CNF)
    {
        $this->CNF = $CNF;
        $this->variables = collect();
        $this->clauses = collect();
        $this->trail = collect();
        $this->decisionLevel = 0;
    }

    public function addVariable($literal)
    {
        $this->variables->push(new Variable($literal));
    }

    public function markVariable($literal, $reason)
    {
        $variable = $this->getVariable($literal);
        $variable->mark($literal, $reason, $this->decisionLevel);
        $this->trail->push($literal);
    }

    public function getVariable($literal)
    {
        if (is_null($literal)) {
            throw new \Exception('null literal detected');
        }
        $index = -1;
        $literal = abs($literal);
        $variable = null;

        $this->variables
            ->each(function ($v, $i) use (&$index, $literal) {
                if ($v->literal() === $literal) {
                    $index = $i;
                    return false;
                }
            });

        if ($index !== -1) {
            $variable = $this->variables->get($index);
        }

        return $variable;
    }

    public function getRandomVariable()
    {
        try {
            $variable = $this->variables
                ->reject(function ($variable) {
                    return $variable->marked;
                })
                ->random();
            return rand(0, 1) == 0 ? $variable->literal() : -$variable->literal();
        } catch (\Throwable $th) {
           return null;
        }
    }

    public function getRandomLiteral($clause)
    {
        try {
            return $clause->literals()
                ->reject(function ($literal) use ($clause) {
                    $found = false;
                    $clause->watched()
                        ->each(function ($watched) use (&$found, $literal) {
                            if ($watched === $literal) {
                                $found = true;
                                return false;
                            }
                        });
                    return $found;
                })
                ->reject(function ($literal) use ($clause) {
                    $variable = $this->getVariable($literal);
                    return $variable->marked;
                })
                ->random();
        } catch (\InvalidArgumentException $th) {
            return null;
        }
    }

    public function addClause($literals)
    {
        $clause = new Clause($literals);

        switch ($clause->literals()->count()) {
            case 0:
                $this->satisfiable = false;
                break;

            case 1:
                $literal = $clause->literals()->first();
                $variable = $this->getVariable($literal);
                if ($variable->unit()) {
                    if (!$variable->equals($literal)) {
                        $this->satisfiable = false;
                    }
                } else {
                    $variable->makeUnit($literal);
                }
                break;

            default:
                $literal = $clause->literals()->get(0);
                $clause->watch($literal);
                $variable = $this->getVariable($literal);
                $variable->watch($clause);

                $literal = $clause->literals()->get(1);
                $clause->watch($literal);
                $variable = $this->getVariable($literal);
                $variable->watch($clause);
                break;
        }

        $this->clauses->push($clause);
    }

    public function getClause($literals)
    {
        $index = -1;
        $clause = null;

        $this->clauses
            ->each(function ($c, $i) use (&$index, $literals) {
                if ($c->literals() === $literals) {
                    $index = $i;
                    return false;
                }
            });

        if ($index !== -1) {
            $clause = $this->clauses->get($index);
        }

        return $clause;
    }

    function backtrack($reason)
    {
        // echo 'backtracking' . PHP_EOL;

        if ($this->decisionLevel === 0) return null;

        $conflicts = collect();

        // echo 'tagging' . PHP_EOL;

        $reason->literals()
            ->each(function ($literal) use ($conflicts) {
                $variable = $this->getVariable($literal);
                if ($variable->decisionLevel === 0) return;
                $variable->tagged = true;
                if ($variable->decisionLevel < $this->decisionLevel) {
                    $conflicts->push($literal);
                }
            });

        $count = $reason->literals()->count() -  $conflicts->count();

        // echo 'UIP' . PHP_EOL;

        $tlevel = $this->trail->count() - 1;
        $literal;

        do {
            if ($tlevel < 0) return null;

            $literal = $this->trail->get($tlevel--);

            // echo 'literal ' . $literal . PHP_EOL;

            $variable = $this->getVariable($literal);
            $variable->marked = false;

            if (!$variable->tagged) continue;
            $variable->tagged = false;

            $count--;

            if ($count <= 0) break;

            if (is_null($variable->reason)) continue;

            for ($i = 1; $i < $variable->reason->literals()->count(); $i++) { 
                $literal = $variable->reason->literals()->get($i);
                $other = $this->getVariable($literal);
                if ($other->marked || $other->decisionLevel === 0) continue;
                if ($other->decisionLevel < $this->decisionLevel) {
                    $conflicts->push($literal);
                } else {
                    $count++;
                }
                $other->tagged = true;
            }

        } while (true);

        // echo 'no good' . PHP_EOL;

        $nogood = collect([-$literal]);
        $blevel = 0;
        for ($i = 0; $i < $conflicts->count(); $i++) {
            $literal = $conflicts->get($i);
            $variable = $this->getVariable($literal);
            if ($variable->reason) {
                if ($variable->reason->literals()->count() > 0) {
                    for ($j = 1; $j < $variable->reason->literals()->count() && $this->getVariable($variable->reason->literals()->get($j))->marked(); $j++);
                    if ($j >= $variable->reason->literals()->count()) continue;
                }
            }
            $nogood->push($literal);
            if ($blevel < $variable->decisionLevel)
            {
                $blevel = $variable->decisionLevel;
                $nogood->put($nogood->count() - 1, $nogood->get(1));
                $nogood->put(1, $literal);
            }
        }

        // echo 'unwind' . PHP_EOL;

        while ($tlevel >= 0)
        {
            $literal = $this->trail->get($tlevel);
            $variable = $this->getVariable($literal);
            if ($variable->decisionLevel <= $blevel) break;
            $variable->marked = false;
            $tlevel--;
        }

        $this->trail = $this->trail->slice(0, $tlevel + 1);

        // echo 'clear' . PHP_EOL;

        for ($i = 0; $i < $conflicts->count(); $i++)
        {
            $variable = $this->getVariable($conflicts->get($i));
            $variable->tagged = false;
        }

        // echo 'learn' . PHP_EOL;

        $this->addClause($nogood);
        $this->decisionLevel = $blevel;

        // echo ($this->satisfiable ? 'true' : 'false') . PHP_EOL;

        if (!$this->satisfiable) return null;

        // echo 'works' . PHP_EOL;

        return $nogood;
    }

    function propagate($literal, $reason = null)
    {
        do {
            $restart = false;
            $current = $this->trail->count();
            $next = $current + 1;

            $this->markVariable($literal, $reason);

            while ($current < $next) {
                $literal = $this->trail->get($current);
                $current++;

                $literal = -$literal;

                $variable = $this->getVariable($literal);

                foreach ($variable->watches() as $watch) {
                    $clause = $this->getClause($watch->literals());
                    if ($clause->contains($variable)) {
                        continue;
                    }
        
                    $clause->unwatch($variable->literal);
                    $variable->unwatch($clause);
        
                    $literal = $this->getRandomLiteral($clause);
                    if ($literal) {
                        $clause->watch($literal);
                        $other = $this->getVariable($literal);
                        $other->watch($clause);
                    } else {
                        if ($clause->watched()->count() === 1) {
                            $other = $this->getVariable($clause->watched()->first());
                            if ($variable->literal() === $other->literal() && $variable->negated() !== $other->negated()) {
                                throw new \Exception('Conflict 1');
                            } else {
                                if (!$other->marked) {
                                    $this->markVariable($clause->watched()->first(), $clause);
                                    $next++;
                                    $continue;
                                }
                            }
                        } else {
                            $reason = $this->backtrack($clause);
                            if (is_null($reason)) return false;
                            $literal = $reason->get(0);
                            $restart = true;
                            break;
                        }
                    }
                }

                if ($restart) {
                    break;
                }
            }
        } while ($restart);
    }

    public function solve()
    {
        // echo PHP_EOL;
        $this->CNF->variables()
            ->each(function ($variable) {
                $this->addVariable($variable);
            });

        $this->CNF->clauses()
            ->each(function ($clause) {
                $this->addClause($clause);
            });

        $this->variables
            ->each(function ($variable) {
                if ($variable->unit()) {
                    $this->propagate($variable->value());
                }
            });

        for ($this->decisionLevel = 1; true; $this->decisionLevel++) {
            $literal = $this->getRandomVariable();
            if ($literal) {
                $this->propagate($literal);
            } else {
                break;
            }
        }

        return $this->variables->map(function ($variable) {
            return $variable->value();
        })->toArray();
    }
}
