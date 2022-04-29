<?php

namespace SAT;

class Variable
{
    public $unit;
    public $marked;
    public $tagged;
    public $negated;
    public $literal;
    public $reason;
    public $watches;
    public $decisionLevel;

    public function __construct($literal)
    {
        $this->unit = false;
        $this->marked = false;
        $this->tagged = false;
        $this->negated = false;
        $this->literal = $literal;
        $this->reason = null;
        $this->watches = collect();
        $this->decisionLevel = 0;
    }

    public function negated()
    {
        return $this->negated;
    }

    public function literal()
    {
        return $this->literal;
    }

    public function equals($other)
    {
        if ($other instanceof Varable) {
            return $this->literal === $other->literal();            
        }

        return ($this->negated ? -$this->literal : $this->literal) === $other;
    }

    public function unit()
    {
        return $this->unit;
    }

    public function value()
    {
        return $this->negated ? -$this->literal : $this->literal;
    }

    public function mark($literal, $reason, $decisionLevel)
    {
        // echo 'marked ' . $literal . ' ' . $decisionLevel . PHP_EOL;
        $this->negated = $literal < 0;
        $this->reason = $reason;
        $this->decisionLevel = $decisionLevel;
        $this->marked = true;
        return $this;
    }

    public function marked()
    {
        return $this->marked;
    }
    
    public function makeUnit($literal)
    {
        // echo 'unit ' . $literal . PHP_EOL;
        $this->negated = $literal < 0;
        $this->unit = true;
        return $this;
    }

    public function unwatch($clause)
    {
        // echo 'unwatching ' . $this->literal . ' ' . implode(',', $clause->literals()->toArray()) . PHP_EOL;

        $index = -1;

        $this->watches
            ->each(function ($watch, $i) use (&$index, $clause) {
                if ($watch->literals() === $clause->literals()) {
                    $index = $i;
                    return false;
                }
            });
        
        if ($index !== -1) {
            $this->watches->forget($index);
        }
    }

    public function watch($clause)
    {
        // echo 'watching ' . $this->literal . ' ' . implode(',', $clause->literals()->toArray()) . PHP_EOL;
        $this->watches->push($clause);
    }

    public function watches()
    {
        return $this->watches;
    }
}
