<?php

namespace SAT;

class BruteSolver
{
    public function __construct($CNF)
    {
        $this->CNF = $CNF;
        $this->variables = collect();
        $this->clauses = collect();
    }

    public function solve()
    {
        return $this->CNF->variables()
            ->skip(1)
            ->reduce(function ($variables, $variable) {
                return $variables->crossJoin([$variable, -$variable]);
            }, collect([$this->CNF->variables()->first(), -$this->CNF->variables()->first()]))
            ->flatten()
            ->chunk($this->CNF->variables()->count())
            ->map(function ($variables) {
                return $variables->values();
            })
            ->shuffle()
            ->first(function ($possibility) {
                return $this->CNF->clauses()
                    ->every(function ($clause) use ($possibility) {
                        return $clause->contains(function ($literal) use ($possibility) {
                            return $literal === $possibility->first(function ($possibility) use ($literal) {
                                return abs($literal) === abs($possibility);
                            });
                        });
                    });
            })
            ?->toArray();
    }
}
