<?php

namespace IPP\Student\opcodes;

class CallOC implements IOpcodes
{
    public string $label;

    /**
     * @var IOpcodes[] $opcodes
     */
    public array $opcodes;

    /**
     * @param IOpcodes[] $opcodes
     */
    public function __construct(array &$opcodes)
    {
        $this->opcodes = &$opcodes;
    }


    //Tato rekurzivní etoda funguje tak, že při nalezení "label" vykoná všechny instrukce od něho, tímto způsobem si nemusím ukládat číslo instrukce "call", na kterou se musím vracet
    public function Execute(int $index): int
    {
        for ($i = 0; $i < count($this->opcodes); $i++)
        {
            if ($this->opcodes[$i] instanceof LabelOC && $this->opcodes[$i]->name == $this->label)
            {
                for ($ind = $i; $ind < count($this->opcodes); $ind++)
                {
                    $opcode = $this->opcodes[$ind];
                    $ret = $opcode->Execute($ind);
                    if ($ret == -2)
                    {
                        return 0;
                    }
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