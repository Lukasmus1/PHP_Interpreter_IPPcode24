<?php

namespace IPP\Student\opcodes;

use IPP\Student\enums\DataTypeEnum;
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
        $var = Tools::FindInFrame($this->var);
        if (is_numeric($var))
        {
            return $var;
        }

        if ($this->sym1 instanceof VarClass)
        {
            $var1 = Tools::FindInFrame($this->sym1);
            if (is_numeric($var1))
            {
                return $var1;
            }
        }
        else
        {
            $var1 = $this->sym1;
        }

        if ($this->sym2 instanceof VarClass)
        {
            $var2 = Tools::FindInFrame($this->sym2);
            if (is_numeric($var2))
            {
                return $var2;
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
        $var->Value = ord(((string)$var1->Value)[(int)($var2->Value)]);
        return 0;
    }
}