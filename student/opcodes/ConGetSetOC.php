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

    public function Execute(int $index) : int
    {
        $var1 = Tools::FindInFrame($this->var->Name);
        if ($var1 == null)
        {
            return 54;
        }

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

        if ($this->type == "concat")
        {
            if ($var2->Type != DataTypeEnum::STRING || $var3->Type != DataTypeEnum::STRING)
            {
                return 53;
            }

            $var1->Value = $var2->Value . $var3->Value;
            $var1->Type = DataTypeEnum::STRING;
            return 0;
        }

        if ($this->type == "getchar")
        {

            if ($var2->Type != DataTypeEnum::STRING || $var3->Type != DataTypeEnum::INT)
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
        else
        {
            if ($var2->Type != DataTypeEnum::INT || $var3->Type != DataTypeEnum::STRING)
            {
                return 53;
            }

            if (strlen((string)$var3->Value) != 1)
            {
                return 58;
            }

            if ($var2->Value < 0 || $var2->Value > strlen((string)$var1->Value) - 1)
            {
                return 58;
            }

            $var1->Value[(int)$var2->Value] = (string)$var3->Value;
        }
        return 0;
    }
}