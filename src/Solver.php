<?php

namespace SAT;

class Solver
{
    public $CNF;
    public $variables;
    public $clauses;
    public $trail;
    public $decisionLevel;
    public $satisfiable;

    public function __construct($CNF)
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

    public function markVariable($literal)
    {
        $variable = $this->getVariable($literal);
        $variable->mark($literal, $this->decisionLevel);
        $this->trail->push($literal);
    }

    public function getVariable($literal)
    {
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

    public function updateVariable($variable)
    {
        $index = -1;

        $this->variables
            ->each(function ($v, $i) use (&$index, $variable) {
                if ($v->equals($variable)) {
                    $index = $i;
                    return false;
                }
            });

        if ($index !== -1) {
            $this->variables->put($index, $variable);
        }

        return $this;
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
                    $this->updateVariable($variable);
                }
                break;
    
            default:
                $literal = $this->getRandomLiteral($clause);
                $clause->watch($literal);
                $variable = $this->getVariable($literal);
                $variable->watch($clause);
                $this->updateVariable($variable);

                $literal = $this->getRandomLiteral($clause);
                $clause->watch($literal);
                $variable = $this->getVariable($literal);
                $variable->watch($clause);
                $this->updateVariable($variable);

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

    function propagate($literal)
    {
        do {
            $restart = false;
            $current = $this->trail->count();
            $next = $current + 1;

            $this->markVariable($literal);

            while ($current < $next) {
                $literal = $this->trail->get($current);
                $current++;

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
                        $this->updateVariable($other);    
                    } else {
                        if ($clause->watched()->count() === 1) {
                            $other = $this->getVariable($clause->watched()->first());
                            if ($variable->literal() === $other->literal() && $variable->negated() !== $other->negated()) {
                                throw new \Exception('Conflict 1');
                            } else {
                                if (!$other->marked) {
                                    $this->markVariable($clause->watched()->first());
                                    $next++;
                                    $continue;
                                }
                            }
                        } else {
                            throw new \Exception('Conflict - [' . $clause->watched()->implode(', ') . ']');
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
        $this->CNF->literals()
            ->each(function ($literal) {
                $this->addVariable($literal);
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

        for ($this->decisionLevel = 0; true; $this->decisionLevel++) {
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
