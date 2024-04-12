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
        //Získání proměnné z rámce
        $var1 = Tools::FindInFrame($this->var);
        if (is_numeric($var1))
        {
            return $var1;
        }

        //Kontrola jestli sym1 je proměnná nebo konstanta
        if ($this->sym1 instanceof VarClass)
        {
            $var2 = Tools::FindInFrame($this->sym1);
            if (is_numeric($var2))
            {
                return $var2;
            }
        }
        else
        {
            $var2 = $this->sym1;
        }

        //Kontrola jestli sym2 je proměnná nebo konstanta
        if ($this->sym2 instanceof VarClass)
        {
            $var3 = Tools::FindInFrame($this->sym2);
            if (is_numeric($var3))
            {
                return $var3;
            }
        }
        else
        {
            $var3 = $this->sym2;
        }

        //Vykonání operace podle jejího názvu

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

            //Tahle kontrola tu je jenom pro phpstan
            if (is_string($var1->Value))
            {
                $var1->Value[(int)$var2->Value] = (string)$var3->Value;
            }
        }
        return 0;
    }
}