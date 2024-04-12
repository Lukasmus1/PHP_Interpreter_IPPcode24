<?php

namespace IPP\Student\opcodes;

use IPP\Student\Frames;
use IPP\Student\TempFrame;

class PopFrameOC implements IOpcodes
{

    public function Execute(int $index): int
    {
        if (!Frames::$localInitialized)
        {
            return 55;
        }

        TempFrame::$TempFrame = array();
        TempFrame::$TempFrame[] = null;

        $idk = array_pop(Frames::$LocalFrame);

        //Doslova pouze pro to aby phpstan neřval :koteseni:
        if ($idk == null)
        {
            return 55;
        }
        foreach ($idk->TempFrame as $item)
        {
            TempFrame::$TempFrame[] = $item;
        }

        return 0;
    }
}