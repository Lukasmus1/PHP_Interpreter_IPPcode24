<?php

namespace IPP\Student\opcodes;

class LabelOC implements IOpcodes
{
    public string $name;

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

    public function Execute(int $index) : int
    {
        for ($i = 0; $i < count($this->opcodes); $i++)
        {
            if ($i == $index)
            {
                continue;
            }
            if ($this->opcodes[$i] instanceof LabelOC && $this->opcodes[$i]->name == $this->name)
            {
                return 52;
            }
        }
        return 0;
    }
}