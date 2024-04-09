<?php

namespace IPP\Student;

use IPP\Student\enums\DataTypeEnum;

class SymClass
{
    public DataTypeEnum $Type;
    public string|int|bool|null $Value;

    public function __construct(DataTypeEnum $type, string|int|bool|null $value)
    {
        $this->Type = $type;
        $this->Value = $value;
    }
}