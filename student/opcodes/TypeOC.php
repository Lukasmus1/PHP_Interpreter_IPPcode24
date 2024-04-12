<?php

namespace IPP\Student\opcodes;

use IPP\Student\enums\DataTypeEnum;
use IPP\Student\SymClass;
use IPP\Student\Tools;
use IPP\Student\VarClass;

class TypeOC implements IOpcodes
{
    public VarClass $var;
    public VarClass|SymClass $sym;
    public function Execute(int $index): int
    {
        $var = Tools::FindInFrame($this->var);
        if (is_numeric($var))
        {
            return $var;
        }

        $var->Type = DataTypeEnum::STRING;
        if ($this->sym instanceof VarClass)
        {
            $var1 = Tools::FindInFrame($this->sym);
            if (is_numeric($var1))
            {
                return $var1;
            }

            if ($var1->Type == null)
            {
                $var->Value = "";
            }
        }
        else
        {
            $var1 = $this->sym;
        }

        switch ($var1->Type)
        {
            case DataTypeEnum::STRING:
                $var->Value = "string";
                break;
            case DataTypeEnum::INT:
                $var->Value = "int";
                break;
            case DataTypeEnum::BOOL:
                $var->Value = "bool";
                break;
            case DataTypeEnum::NIL:
                $var->Value = "nil";
                break;
            default:
                break;
        }
        return 0;
    }
}