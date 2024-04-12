<?php

namespace IPP\Student\opcodes;

use IPP\Student\enums\DataTypeEnum;
use IPP\Student\SymClass;
use IPP\Student\Tools;
use IPP\Student\VarClass;

class AddSubMulDivOC implements IOpcodes
{
    public VarClass $var;
    public VarClass|SymClass $sym1;
    public VarClass|SymClass $sym2;

    public string $type;

    public function __construct(string $type)
    {
        $this->type = $type;
    }


    public function Execute(int $index): int
    {
        //Kontrola jestli sym1 je proměnná nebo konstanta
        if ($this->sym1 instanceof VarClass)
        {
            $var1 = Tools::FindInFrame($this->sym1);
            if (is_numeric($var1))
            {
                return $var1;
            }
            $this->sym1 = $var1;
            if (!is_numeric($this->sym1->Value))
            {
                return 53;
            }
        }


        if ($this->sym1->Type != DataTypeEnum::INT)
        {
            return 53;
        }
        $num1 = $this->sym1->Value;

        //Kontrola jestli sym2 je proměnná nebo konstanta
        if ($this->sym2 instanceof VarClass)
        {
            $var2 = Tools::FindInFrame($this->sym2);
            if (is_numeric($var2))
            {
                return $var2;
            }
            $this->sym2 = $var2;
            if (!is_numeric($this->sym2->Value))
            {
                return 53;
            }
        }

        if ($this->sym2->Type != DataTypeEnum::INT)
        {
            return 53;
        }
        if($this->sym2->Value == 0)
        {
            return 57;
        }
        $num2 = $this->sym2->Value;

        //Získání proměnné ze zásobníku
        $var2 = Tools::FindInFrame($this->var);
        if (is_numeric($var2))
        {
            return $var2;
        }

        switch ($this->type)
        {
            case "idiv":
                $var2->Value = (int)$num1 / (int)$num2;
                break;
            case "add":
                $var2->Value = (int)$num1 + (int)$num2;
                break;
            case "sub":
                $var2->Value = (int)$num1 - (int)$num2;
                break;
            case "mul":
                $var2->Value = (int)$num1 * (int)$num2;
                break;
        }
        $var2->Type = DataTypeEnum::INT;
        return 0;
    }
}