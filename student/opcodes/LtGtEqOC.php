<?php

namespace IPP\Student\opcodes;

use IPP\Student\enums\DataTypeEnum;
use IPP\Student\SymClass;
use IPP\Student\Tools;
use IPP\Student\VarClass;
use PhpParser\Node\Stmt\Else_;

class LtGtEqOC implements IOpcodes
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
        $var1 = $this->sym1;
        $var2 = $this->sym2;

        if ($this->sym1 instanceof VarClass)
        {
            $var1 = Tools::FindInFrame($this->sym1->Name);
            if ($var1 == null)
            {
                return 54;
            }
        }

        if ($this->sym2 instanceof VarClass)
        {
            $var2 = Tools::FindInFrame($this->sym2->Name);
            if ($var2 == null)
            {
                return 54;
            }
        }

        $var = Tools::FindInFrame($this->var->Name);
        if ($var == null)
        {
            return 54;
        }
        $var->Type = DataTypeEnum::BOOL;


        if (($var1->Type == DataTypeEnum::NIL || $var2->Type == DataTypeEnum::NIL))
        {
            if ($this->type == "eq")
            {
                if ($var1->Type == $var2->Type)
                {
                    $var->Value = true;
                    return 0;
                }
                $var->Value = false;
                return 0;
            }
            else
            {
                return 53;
            }
        }

        if ($var1->Type != $var2->Type)
        {
            return 53;
        }

        switch ($var1->Type)
        {
            case DataTypeEnum::INT:
                if ($this->type == "lt")
                {
                    $var->Value = (int)$var1->Value < (int)$var2->Value;
                }
                else if ($this->type == "eq")
                {
                    $var->Value = (int)$var1->Value == (int)$var2->Value;
                }
                else
                {
                    $var->Value = (int)$var1->Value > (int)$var2->Value;
                }
                return 0;
            case DataTypeEnum::BOOL:
                if ($this->type == "lt")
                {
                    $var->Value = filter_var($var1->Value, FILTER_VALIDATE_BOOLEAN) < filter_var($var2->Value, FILTER_VALIDATE_BOOLEAN);
                }
                else if ($this->type == "eq")
                {
                    $var->Value = filter_var($var1->Value, FILTER_VALIDATE_BOOLEAN) == filter_var($var2->Value, FILTER_VALIDATE_BOOLEAN);
                }
                else
                {
                    $var->Value = filter_var($var1->Value, FILTER_VALIDATE_BOOLEAN) > filter_var($var2->Value, FILTER_VALIDATE_BOOLEAN);
                }
                return 0;
            case DataTypeEnum::STRING:
                if ($this->type == "lt")
                {
                    $var->Value = (string)$var1->Value < (string)$var2->Value;
                }
                else if ($this->type == "eq")
                {
                    $var->Value = (string)$var1->Value == (string)$var2->Value;
                }
                else
                {
                    $var->Value = (string)$var1->Value > (string)$var2->Value;
                }
                return 0;
        }


        return 0;
    }
}