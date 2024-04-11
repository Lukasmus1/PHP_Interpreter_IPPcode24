<?php

namespace IPP\Student\opcodes;

use IPP\Student\enums\DataTypeEnum;
use IPP\Student\opcodes\IOpcodes;
use IPP\Student\SymClass;
use IPP\Student\Tools;
use IPP\Student\VarClass;

class Int2CharOC implements IOpcodes
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

        if ($var1->Type != DataTypeEnum::INT)
        {
            return 53;
        }

        $char = mb_chr((int)$var1->Value, "UTF-8");
        if ($char == null)
        {
            return 58;
        }

        $var->Type = DataTypeEnum::STRING;
        $var->Value = $char;
        return 0;
    }
}