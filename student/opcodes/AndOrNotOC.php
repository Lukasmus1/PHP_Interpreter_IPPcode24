<?php

namespace IPP\Student\opcodes;

use IPP\Student\enums\DataTypeEnum;
use IPP\Student\opcodes\IOpcodes;
use IPP\Student\SymClass;
use IPP\Student\Tools;
use IPP\Student\VarClass;

class AndOrNotOC implements IOpcodes
{
    public VarClass $var;
    public VarClass|SymClass $sym1;
    public VarClass|SymClass $sym2;
    public string $type;

    public function __construct(string $s)
    {
        $this->type = $s;
    }

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
        }
        else
        {
            $var2 = $this->sym2;
        }

        if ($var1->Type != DataTypeEnum::BOOL || $var2->Type != DataTypeEnum::BOOL)
        {
            return 53;
        }

        $var->Type = DataTypeEnum::BOOL;
        switch ($this->type)
        {
            case 'and':
                $var->Value = filter_var($var1->Value, FILTER_VALIDATE_BOOLEAN) && filter_var($var2->Value, FILTER_VALIDATE_BOOLEAN);
                break;
            case 'or':
                $var->Value = filter_var($var1->Value, FILTER_VALIDATE_BOOLEAN) || filter_var($var2->Value, FILTER_VALIDATE_BOOLEAN);
                break;
            case 'not':
                $var->Value = !filter_var($var1->Value, FILTER_VALIDATE_BOOLEAN);
                break;

        }
        return 0;
    }
}