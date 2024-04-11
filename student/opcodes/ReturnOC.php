<?php

namespace IPP\Student\opcodes;

use IPP\Student\opcodes\IOpcodes;

class ReturnOC implements IOpcodes
{

    public function Execute(int $index): int
    {
        return -2;
    }
}