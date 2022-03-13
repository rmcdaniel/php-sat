<?php

namespace SAT;

class CNF
{
    public $clauses;

    public function __construct($clauses)
    {
        $this->clauses = collect();

        foreach ($clauses as $clause) {
            $this->clauses->push(collect($clause));
        }
    }

    public static function toFormula($literals)
    {
        $alpha = function ($literal)
        {
            $literal -= 1;
    
            for ($alpha = ''; $literal >= 0; $literal = intval($literal / 26) - 1) {
                $alpha = chr($literal%26 + 0x41) . $alpha;
            }
    
            return $alpha;
        };

        $literals = collect($literals);

        $formula = $literals
            ->map(function($literals) use ($alpha) {
                return '(' . collect($literals)
                    ->map(function($literal) use ($alpha) {
                        if ($literal < 0) {
                            return 'not ' . $alpha(abs($literal));
                        } else {
                            return $alpha(abs($literal));
                        }
                    })->implode(' or ') . ')';
            })->implode(' and ');

        if ($literals->count() > 1) {
            $formula = '(' . $formula . ')';
        }

        return $formula;
    }

    public function variables()
    {
        return $this->clauses
            ->flatMap(function ($literals) {
                return $literals->map(function ($literal) {
                    return abs($literal);
                });
            })
            ->unique()
            ->sort();
    }

    public function clauses()
    {
        return $this->clauses;
    }
}
