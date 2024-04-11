<?php

namespace IPP\Student\opcodes;

use IPP\Student\enums\DataTypeEnum;
use IPP\Student\opcodes\IOpcodes;
use IPP\Student\SymClass;
use IPP\Student\Tools;
use IPP\Student\VarClass;

class StrlenOC implements IOpcodes
{
    public VarClass $var;
    public VarClass|SymClass $sym;

    public function Execute(int $index): int
    {
        $var = Tools::FindInFrame($this->var->Name);
        if ($var == null)
        {
            return 54;
        }

        if ($this->sym instanceof VarClass)
        {
            $var1 = Tools::FindInFrame($this->sym->Name);
            if ($var1 == null)
            {
                return 54;
            }
        }
        else
        {
            $var1 = $this->sym;
        }

        if ($var1->Type != DataTypeEnum::STRING)
        {
            return 53;
        }

        $var->Type = DataTypeEnum::INT;
        $var->Value = strlen((string)$var1->Value);
        return 0;
    }
}