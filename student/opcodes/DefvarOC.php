<?php

namespace IPP\Student\opcodes;

use IPP\Core\Exception\IntegrationException;
use IPP\Student\GlobalFrame;
use IPP\Student\opcodes\IOpcodes;
use IPP\Student\Tools;
use IPP\Student\VarClass;

class DefvarOC implements IOpcodes
{
    public VarClass $Sym;

    public function Execute(): void
    {
        if(Tools::PushToFrame($this->Sym))
        {
            return;
        }
        else
        {
            throw new IntegrationException();
        }
    }
}