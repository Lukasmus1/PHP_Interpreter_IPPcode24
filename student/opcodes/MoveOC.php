<?php

namespace IPP\Student\opcodes;

use IPP\Student\enums\DataTypeEnum;
use IPP\Student\SymClass;
use IPP\Student\Tools;
use IPP\Student\VarClass;

class MoveOC implements IOpcodes
{
    public VarClass $var;
    public VarClass|SymClass $sym;

    public function Execute(int $index): int
    {
        //Získání proměnné z rámce
        $var1 = Tools::FindInFrame($this->var);
        if (is_numeric($var1))
        {
            return $var1;
        }
        else
        {
            //Kontrola jestli sym je proměnná nebo konstanta
            if ($this->sym instanceof VarClass)
            {
                $var2 = Tools::FindInFrame($this->sym);
                if (is_numeric($var2))
                {
                    return $var2;
                }

                $var1->Value = $var2->Value;
                $var1->Type = $var2->Type;
                return 0;
            }

            switch ($this->sym->Type)
            {
                case DataTypeEnum::INT:
                    $var1->Value = (int)$this->sym->Value;
                    break;
                case DataTypeEnum::BOOL:
                    $var1->Value = filter_var($this->sym->Value, FILTER_VALIDATE_BOOLEAN);
                    break;
                case DataTypeEnum::STRING:
                case DataTypeEnum::NIL:
                    $var1->Value = $this->sym->Value;
                    break;
                default:
                    //Tady se to nikdy nedostane
                    return 59;
            }

            $var1->Type = $this->sym->Type;
        }
        return 0;
    }
}