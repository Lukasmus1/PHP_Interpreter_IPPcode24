<?php

namespace IPP\Student\opcodes;

use IPP\Student\TempFrame;

class CreateFrameOC implements IOpcodes
{

    public function Execute(int $index): int
    {
        TempFrame::$TempFrame = array();
        TempFrame::$TempFrame[0] = null;
        return 0;
    }
}