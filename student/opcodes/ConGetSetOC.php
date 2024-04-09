<?php

namespace IPP\Student\opcodes;

use IPP\Student\enums\DataTypeEnum;
use IPP\Student\SymClass;
use IPP\Student\Tools;
use IPP\Student\VarClass;

class ConGetSetOC implements IOpcodes
{
    public VarClass $var;
    public VarClass|SymClass $sym1;
    public VarClass|SymClass $sym2;
    public string $type;

    public function __construct(string $type)
    {
        $this->type = $type;
    }

    public function Execute() : int
    {
        $var1 = Tools::FindInFrame($this->var->Name);
        if ($var1 == null)
        {
            return 54;
        }

        if ($this->type == "getchar" || $this->type == "setchar")
        {
            if ($this->sym1 instanceof VarClass)
            {
                $var2 = Tools::FindInFrame($this->sym1->Name);
                if ($var2 == null)
                {
                    return 54;
                }
            }
            else
            {
                $var2 = $this->sym1;
            }

            if ($var2->Type != DataTypeEnum::STRING)
            {
                return 53;
            }

            if ($this->sym2 instanceof VarClass)
            {
                $var3 = Tools::FindInFrame($this->sym2->Name);
                if ($var3 == null)
                {
                    return 54;
                }
            }
            else
            {
                $var3 = $this->sym2;
            }

            if ($var3->Type != DataTypeEnum::INT)
            {
                return 53;
            }

            $ind = $var3->Value;

            if ($ind > strlen((string)$var2->Value) - 1)
            {
                return 58;
            }

            $string = (string)$var2->Value;
            $var1->Value = $string[(int)$ind];
            $var1->Type = DataTypeEnum::STRING;
        }


        return 0;
    }
}