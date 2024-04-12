<?php

namespace IPP\Student\opcodes;

use IPP\Student\Frames;
use IPP\Student\TempFrame;
use IPP\Student\TempInstance;

class PushFrame implements IOpcodes
{

    public function Execute(int $index): int
    {
        $item = new TempInstance();
        if (count(TempFrame::$TempFrame) == 0)
        {
            return 55;
        }
        foreach (TempFrame::$TempFrame as $tf)
        {
            if ($tf == null)
            {
                continue;
            }
            $item->TempFrame[] = $tf;
        }

        Frames::$LocalFrame[] = $item;
        Frames::$localInitialized = true;
        TempFrame::$TempFrame = array();
        return 0;
    }
}