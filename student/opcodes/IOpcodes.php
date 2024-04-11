<?php

namespace IPP\Student\opcodes;

interface IOpcodes
{
    public function Execute(int $index) : int;
}