<?php

namespace IPP\Student\opcodes;

use IPP\Student\Frames;
use IPP\Student\SymClass;
use IPP\Student\Tools;
use IPP\Student\VarClass;

class PushsOC implements IOpcodes
{
    public VarClass|SymClass $sym;

    public function Execute(int $index): int
    {
        if ($this->sym instanceof VarClass)
        {
            $var = Tools::FindInFrame($this->sym);
            if (is_numeric($var))
            {
                return $var;
            }
        }
        else
        {
            $var = $this->sym;
        }

        Frames::$Frame[] = clone $var;
        return 0;
    }
}