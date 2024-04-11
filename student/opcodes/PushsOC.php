<?php

namespace IPP\Student\opcodes;

use IPP\Student\Frames;
use IPP\Student\opcodes\IOpcodes;
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
            $var = Tools::FindInFrame($this->sym->Name);
            if ($var == null)
            {
                return 54;
            }
        }
        else
        {
            $var = $this->sym;
        }

        array_push(Frames::$Frame, clone $var);
        return 0;
    }
}