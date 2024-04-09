<?php

namespace IPP\Student\opcodes;

use IPP\Core\Exception\IntegrationException;
use IPP\Student\GlobalFrame;
use IPP\Student\opcodes\IOpcodes;
use IPP\Student\Tools;
use IPP\Student\VarClass;

class DefvarOC implements IOpcodes
{
    public VarClass $var;

    public function Execute(): int
    {
        if(Tools::PushToFrame($this->var))
        {
            return 0;
        }
        else
        {
            return 54;
        }
    }
}