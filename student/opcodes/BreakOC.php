<?php

namespace IPP\Student\opcodes;

use IPP\Core\Interface\OutputWriter;

class BreakOC implements IOpcodes
{

    private OutputWriter $outWriteErr;

    public function __construct(OutputWriter $out)
    {
        $this->outWriteErr = $out;
    }
    public function Execute(int $index): int
    {
        $this->outWriteErr->writeString("Break se vykonal jako instrukce na pozici: " . $index);
        return 0;
    }
}