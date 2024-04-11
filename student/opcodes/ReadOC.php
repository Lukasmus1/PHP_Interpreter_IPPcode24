<?php

namespace IPP\Student\opcodes;

use IPP\Core\Interface\InputReader;
use IPP\Student\enums\DataTypeEnum;
use IPP\Student\opcodes\IOpcodes;
use IPP\Student\Tools;
use IPP\Student\VarClass;

class ReadOC implements IOpcodes
{
    public VarClass $var;
    public string $type;
    private InputReader $reader;

    public function __construct(InputReader $rd)
    {
        $this->reader = $rd;
    }

    public function Execute(int $index): int
    {
        $var1 = Tools::FindInFrame($this->var->Name);
        if ($var1 == null)
        {
            return 54;
        }

        switch ($this->type)
        {
            case "int":
                $input = $this->reader->readInt();
                $var1->Type = DataTypeEnum::INT;
                break;
            case "string":
                $input = $this->reader->readString();
                $var1->Type = DataTypeEnum::STRING;
                break;
            case "bool":
                $input = $this->reader->readBool();
                $var1->Type = DataTypeEnum::BOOL;
                break;
            default:
                $input = null;
                break;
        }

        if ($input == null)
        {
            $var1->Value = "nil";
            $var1->Type = DataTypeEnum::NIL;
            return 0;
        }

        $var1->Value = $input;

        return 0;
    }
}