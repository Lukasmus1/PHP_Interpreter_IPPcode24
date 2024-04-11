<?php

namespace IPP\Student\opcodes;

use IPP\Student\ExceptionClass;
use IPP\Student\opcodes\IOpcodes;

class CallOC implements IOpcodes
{
    public string $label;

    /**
     * @param IOpcodes[] $opcodes
     */
    public array $opcodes;

    /**
     * @param IOpcodes[] $opcodes
     */
    public function __construct(array &$opcodes)
    {
        $this->opcodes = &$opcodes;
    }


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