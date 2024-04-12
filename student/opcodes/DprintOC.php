<?php

namespace IPP\Student\opcodes;

use IPP\Core\Interface\OutputWriter;
use IPP\Student\enums\DataTypeEnum;
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
    }

    public function Execute(int $index): int
    {
        if ($this->sym instanceof VarClass)
        {
            //Získání proměnné z rámce
            $var = Tools::FindInFrame($this->sym);
            if (is_numeric($var))
            {
                return $var;
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
                $this->outWriteErr->writeInt((int)$var->Value);
                break;
            case DataTypeEnum::BOOL:
                $this->outWriteErr->writeBool(filter_var($var->Value, FILTER_VALIDATE_BOOLEAN));
                break;
            case DataTypeEnum::NIL:
                $this->outWriteErr->writeString("");
                break;
            default:
                //Tady se to nikdy nedostane
                return 59;

        }
        return 0;
    }
}