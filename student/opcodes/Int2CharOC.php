<?php

namespace IPP\Student\opcodes;

use IPP\Student\enums\DataTypeEnum;
use IPP\Student\SymClass;
use IPP\Student\Tools;
use IPP\Student\VarClass;

class Int2CharOC implements IOpcodes
{
    public VarClass $var;
    public VarClass|SymClass $sym;

    public function Execute(int $index): int
    {
        //Získání proměnné z rámce
        $var = Tools::FindInFrame($this->var);
        if (is_numeric($var))
        {
            return $var;
        }

        //Kontrola jestli sym je proměnná nebo konstanta
        if ($this->sym instanceof VarClass)
        {
            $var1 = Tools::FindInFrame($this->sym);
            if (is_numeric($var1))
            {
                return $var1;
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