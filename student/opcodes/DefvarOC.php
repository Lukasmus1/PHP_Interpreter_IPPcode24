<?php

namespace IPP\Student\opcodes;

use IPP\Student\Tools;
use IPP\Student\VarClass;

class DefvarOC implements IOpcodes
{
    public VarClass $var;

    public function Execute(int $index): int
    {
        $res = Tools::PushToFrame($this->var);
        if($res != 0)
        {
            return $res;
        }

        return 0;
    }
}