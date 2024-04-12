<?php

namespace IPP\Student;

use IPP\Student\enums\DataTypeEnum;

//Třída pro uchování symbolů pro instrukce
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