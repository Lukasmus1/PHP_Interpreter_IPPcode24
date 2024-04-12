<?php

namespace IPP\Student\opcodes;


class ReturnOC implements IOpcodes
{

    public function Execute(int $index): int
    {
        //Na tuhle hodnotu reagujou metody, které volají execute (například jump...)
        return -2;
    }
}