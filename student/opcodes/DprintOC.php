<?php

namespace IPP\Student\opcodes;

use IPP\Core\Interface\OutputWriter;
use IPP\Student\enums\DataTypeEnum;
use IPP\Student\opcodes\IOpcodes;
use IPP\Student\SymClass;
use IPP\Student\Tools;
use IPP\Student\VarClass;

class DprintOC implements IOpcodes
{
    public VarClass|SymClass $sym;

    private OutputWriter $outWriteErr;

    public function __construct(OutputWriter $out)
    {
        $this->outWriteErr = $out;
        $sym = null;
    }

    public function Execute(int $index): int
    {
        if ($this->sym instanceof VarClass)
        {
            $var = Tools::FindInFrame($this->sym->Name);
            if ($var == null)
            {
                return 54;
            }
        }
        else
        {
            $var = $this->sym;
        }

        switch ($var->Type)
        {
            case DataTypeEnum::STRING:
                $newString = preg_replace_callback('/\\\\(\d{3})/', function ($matches) {
                    return mb_convert_encoding(pack('n', $matches[1]), 'UTF-8', 'UCS-2BE');
                }, (string)$var->Value);
                $this->outWriteErr->writeString((string)$newString);
                break;
            case DataTypeEnum::INT:
                $this->outWriteErr->writeInt($var->Value);
                break;
            case DataTypeEnum::BOOL:
                $this->outWriteErr->writeBool(filter_var($var->Value, FILTER_VALIDATE_BOOLEAN));
                break;
            case DataTypeEnum::NIL:
                $this->outWriteErr->writeString("");
                break;
        }
        return 0;
    }
}