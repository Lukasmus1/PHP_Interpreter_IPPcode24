<?php

namespace IPP\Student\opcodes;

use IPP\Student\opcodes\IOpcodes;

class JumpOC implements IOpcodes
{
    public string $name;

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