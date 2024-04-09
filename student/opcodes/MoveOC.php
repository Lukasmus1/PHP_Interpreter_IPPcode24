<?php

namespace IPP\Student\opcodes;

use IPP\Core\Exception\IntegrationException;
use IPP\Student\opcodes\IOpcodes;
use IPP\Student\SymClass;
use IPP\Student\Tools;
use IPP\Student\VarClass;

class MoveOC implements IOpcodes
{
    public VarClass $var;
    public VarClass|SymClass $sym;

    public function Execute(): void
    {
        $var1 = Tools::FindInFrame($this->var->Name);
        if ($var1 == null)
        {
            throw new IntegrationException();
        }
        else
        {
            if ($this->sym instanceof VarClass)
            {
                $var2 = Tools::FindInFrame($this->var->Name);
                if ($var2 == null)
                {
                    throw new IntegrationException();
                }
                $var1->Value = $var2->Value;
                $var1->Type = $var2->Type;
                return;
            }

            $var1->Value = $this->sym->Value;
            $var1->Type = $this->sym->Type;
        }
    }
}