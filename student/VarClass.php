<?php

namespace IPP\Student;

use IPP\Student\enums\DataTypeEnum;
use IPP\Student\enums\FramesEnum;

//Třída pro uchování proměnných pro instrukce
class VarClass
{
    public FramesEnum $Frame;
    public string $Name;
    public DataTypeEnum|null $Type;
    public string|int|bool|null $Value;

    public function __construct(FramesEnum $fr, string $name, DataTypeEnum $type = null, string|int|bool|null $value = null)
    {
        $this->Frame = $fr;
        $this->Name = $name;
        $this->Type = $type;
        $this->Value = $value;
    }
}