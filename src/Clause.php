<?php

namespace SAT;

class Clause
{
    public $literals;
    public $watched;

    public function __construct($literals)
    {
        $this->literals = collect($literals);
        $this->watched = collect();
    }

    public function literals()
    {
        return $this->literals;
    }

    public function contains($variable)
    {
        $found = false;

        $this->literals
            ->each(function ($literal) use (&$found, $variable) {
                if ($variable->equals($literal)) {
                    $found = true;
                    return false;
                }
            });

        return $found;
    }

    public function unwatch($literal)
    {
        $index = -1;

        $this->watched
            ->each(function ($watch, $i) use (&$index, $literal) {
                if (abs($watch) === $literal) {
                    $index = $i;
                    return false;
                }
            });
        
        if ($index !== -1) {
            $this->watched->forget($index);
        }
    }

    public function watch($literal)
    {
        $this->watched->push($literal);
    }

    public function watched()
    {
        return $this->watched;
    }
}
