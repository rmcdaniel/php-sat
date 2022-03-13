<?php

namespace SAT;

use SplFileObject;

class Parser
{
    public $clauses;

    public function __construct()
    {
        $this->clauses = collect();
    }

    public function load($fileName)
    {
        $file = new SplFileObject($fileName);

        while (!$file->eof()) {
            $line = $file->fgets();
            $this->parse($line);
        }

        $file = null;

        return new CNF($this->clauses);
    }

    public function parse($line)
    {
        $type = substr($line, 0, 1);

        switch ($type) {
            case 'c':
                $this->comment($line);
                break;

            case 'p':
                $this->header($line);
                break;
            
            default:
                $this->clause($line);
                break;
        }
    }

    public function comment($comment)
    {
        //
    }

    public function header($header)
    {
        //
    }

    public function clause($clause)
    {
        $fields = collect(explode(' ', $clause));

        if (count($fields) <= 1) return;

        $this->clauses->push(
            $fields
                ->take($fields->count() - 1)
                ->map(function ($field) {
                    return (int) $field;
                })
        );
    }
}
