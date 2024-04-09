<?php

namespace IPP\Student\opcodes;

use IPP\Core\Exception\IntegrationException;
use IPP\Student\enums\DataTypeEnum;
use IPP\Student\opcodes\IOpcodes;
use IPP\Student\SymClass;
use IPP\Student\Tools;
use IPP\Student\VarClass;

class MoveOC implements IOpcodes
{
    public VarClass $var;
    public VarClass|SymClass $sym;

    public function Execute(): int
    {
        $var1 = Tools::FindInFrame($this->var->Name);
        if ($var1 == null)
        {
            return 54;
        }
        else
        {
            if ($this->sym instanceof VarClass)
            {
                $var2 = Tools::FindInFrame($this->var->Name);
                if ($var2 == null)
                {
                    return 54;
                }

                if (!Tools::TypeChecker($var2->Type, $var2->Value))
                {
                    return 53;
                }
                $var1->Value = $var2->Value;
                $var1->Type = $var2->Type;
                return 0;
            }

            if (!Tools::TypeChecker($this->sym->Type, $this->sym->Value))
            {
                return 53;
            }
            $var1->Value = $this->sym->Value;
            $var1->Type = $this->sym->Type;
        }
        return 0;
    }
}