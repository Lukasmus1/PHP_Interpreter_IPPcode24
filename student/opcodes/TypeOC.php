<?php

namespace IPP\Student\opcodes;

use IPP\Student\enums\DataTypeEnum;
use IPP\Student\opcodes\IOpcodes;
use IPP\Student\SymClass;
use IPP\Student\Tools;
use IPP\Student\VarClass;
use function _PHPStan_11268e5ee\RingCentral\Psr7\str;

class TypeOC implements IOpcodes
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

        $var->Type = DataTypeEnum::STRING;
        if ($this->sym instanceof VarClass)
        {
            $var1 = Tools::FindInFrame($this->sym->Name);
            if ($var1 == null)
            {
                return 54;
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
        }
        return 0;
    }
}