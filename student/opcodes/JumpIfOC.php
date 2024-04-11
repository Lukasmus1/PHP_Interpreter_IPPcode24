<?php

namespace IPP\Student\opcodes;

use IPP\Student\enums\DataTypeEnum;
use IPP\Student\opcodes\IOpcodes;
use IPP\Student\SymClass;
use IPP\Student\Tools;
use IPP\Student\VarClass;

class JumpIfOC implements IOpcodes
{
    public string $name;
    public VarClass|SymClass $sym1;
    public VarClass|SymClass $sym2;
    public string $type;
    /**
     * @param IOpcodes[] $opcodes
     */
    public array $opcodes;

    /**
     * @param IOpcodes[] $opcodes
     */
    public function __construct(array &$opcodes, string $type)
    {
        $this->opcodes = &$opcodes;
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

        if ($var1->Type == DataTypeEnum::NIL || $var2->Type == DataTypeEnum::NIL && $var1->Type != $var2->Type)
        {
            if($this->type == "neq")
            {
                return $this->Jump();
            }
            else
            {
                return 0;
            }
        }

        if ($var1->Type != $var2->Type)
        {
            return 53;
        }

        if ($var1->Value == $var2->Value)
        {
            if ($this->type == "eq")
            {
                return $this->Jump();
            }
            else
            {
                return 0;
            }
        }
        else
        {
            if ($this->type == "neq")
            {
                return $this->Jump();
            }
            else
            {
                return 0;
            }
        }
    }

    private function Jump() : int
    {
        for ($i = 0; $i < count($this->opcodes); $i++)
        {
            if ($this->opcodes[$i] instanceof LabelOC && $this->opcodes[$i]->name == $this->name)
            {
                for ($ind = $i; $ind < count($this->opcodes); $ind++)
                {
                    $opcode = $this->opcodes[$ind];
                    $ret = $opcode->Execute($ind);
                    if ($ret != 0)
                    {
                        return $ret;
                    }
                }
                return -1;
            }
        }
        return 52;
    }
}