<?php

namespace IPP\Student\opcodes;

use IPP\Student\enums\DataTypeEnum;
use IPP\Student\ExceptionClass;
use IPP\Student\opcodes\IOpcodes;
use IPP\Student\SymClass;
use IPP\Student\Tools;
use IPP\Student\VarClass;

class Stri2IntOC implements IOpcodes
{
    public VarClass $var;
    public VarClass|SymClass $sym1;
    public VarClass|SymClass $sym2;

    public function Execute(int $index): int
    {
        $var = Tools::FindInFrame($this->var->Name);
        if ($var == null)
        {
            return 54;
        }

        if ($this->sym1 instanceof VarClass)
        {
            $var1 = Tools::FindInFrame($this->sym1->Name);
            if ($var1 == null)
            {
                return 54;
            }
        }
        else
        {
            $var1 = $this->sym1;
        }

        if ($this->sym2 instanceof VarClass)
        {
            $var2 = Tools::FindInFrame($this->sym2->Name);
            if ($var2 == null)
            {
                return 54;
            }
            if (!is_numeric($var2->Value))
            {
                return 53;
            }
        }
        else
        {
            $var2 = $this->sym2;
        }

        if ($var1->Type != DataTypeEnum::STRING)
        {
            return 53;
        }

        if ($var2->Value > strlen((string)$var1->Value) - 1)
        {
            return 58;
        }

        $var->Type = DataTypeEnum::INT;
        $var->Value = ord((string)$var1->Value[(int)($var2->Value)]);
        return 0;
    }
}