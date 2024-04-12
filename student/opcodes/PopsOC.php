<?php

namespace IPP\Student\opcodes;

use IPP\Student\Frames;
use IPP\Student\Tools;
use IPP\Student\VarClass;

class PopsOC implements IOpcodes
{
    public VarClass $var;

    public function Execute(int $index): int
    {
        $var = Tools::FindInFrame($this->var);
        if (is_numeric($var))
        {
            return $var;
        }

        $popped = array_pop(Frames::$Frame);
        if ($popped == null)
        {
            return 56;
        }

        $var->Value = $popped->Value;
        $var->Type = $popped->Type;

        return 0;
    }
}