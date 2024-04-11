<?php

namespace IPP\Student\opcodes;

use IPP\Core\Interface\OutputWriter;
use IPP\Student\opcodes\IOpcodes;

class BreakOC implements IOpcodes
{

    private OutputWriter $outWriteErr;

    public function __construct(OutputWriter $out)
    {
        $this->outWriteErr = $out;
        $sym = null;
    }
    public function Execute(int $index): int
    {
        //$this->outWriteErr->writeString("Break se vykonal jako instrukce na pozici ");
        return 0;
    }
}