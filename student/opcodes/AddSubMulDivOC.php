<?php

namespace IPP\Student\opcodes;

use _PHPStan_11268e5ee\Nette\Utils\Type;
use IPP\Core\Exception\IntegrationException;
use IPP\Student\enums\DataTypeEnum;
use IPP\Student\opcodes\IOpcodes;
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
        if ($this->sym1 instanceof VarClass)
        {
            $var1 = Tools::FindInFrame($this->sym1->Name);
            if ($var1 == null)
            {
                return 54;
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

        if ($this->sym2 instanceof VarClass)
        {
            $var2 = Tools::FindInFrame($this->sym2->Name);
            if ($var2 == null)
            {
                return 54;
            }
            $this->sym2 = $var2;
            if (!is_numeric($this->sym2->Value))
            {
                return 53;
            }
        }

        //TODO - FIX RETURNUNG ERR CODES (MIGHT WANNA REDESIGN EXECUTE FUNCTION)
        if ($this->sym2->Type != DataTypeEnum::INT)
        {
            return 53;
        }
        if($this->sym2->Value == 0)
        {
            return 57;
        }
        $num2 = $this->sym2->Value;

        $var2 = Tools::FindInFrame($this->var->Name);
        if ($var2 == null)
        {
            return 54;
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