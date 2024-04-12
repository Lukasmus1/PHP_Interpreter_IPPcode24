<?php

namespace IPP\Student\opcodes;

use IPP\Core\Interface\OutputWriter;
use IPP\Student\enums\DataTypeEnum;
use IPP\Student\SymClass;
use IPP\Student\Tools;
use IPP\Student\VarClass;

class WriteOC implements IOpcodes
{
    public VarClass|SymClass $sym;
    private OutputWriter $outWrite;

    public function __construct(OutputWriter $out)
    {
        $this->outWrite = $out;
    }

    public function Execute(int $index) : int
    {
        if ($this->sym instanceof VarClass)
        {
            $object = Tools::FindInFrame($this->sym);
            if (is_numeric($object))
            {
                return $object;
            }

            switch ($object->Type)
            {
                case DataTypeEnum::STRING:
                    $newString = preg_replace_callback('/\\\\(\d{3})/', function ($matches)
                    {
                        return mb_convert_encoding(pack('n', $matches[1]), 'UTF-8', 'UCS-2BE');
                    }, (string)$object->Value);
                    $this->outWrite->writeString((string)$newString);
                    break;
                case DataTypeEnum::INT:
                    $this->outWrite->writeInt((int)$object->Value);
                    break;
                case DataTypeEnum::BOOL:
                    $this->outWrite->writeBool(filter_var($object->Value, FILTER_VALIDATE_BOOLEAN));
                    break;
                case DataTypeEnum::NIL:
                    $this->outWrite->writeString("");
                    break;
                default:
                    //Tady se to nikdy nedostane
                    return 59;
            }
            return 0;
        }

        switch ($this->sym->Type)
        {
            case DataTypeEnum::STRING:
                $newString = preg_replace_callback('/\\\\(\d{3})/', function ($matches)
                {
                    return mb_convert_encoding(pack('n', $matches[1]), 'UTF-8', 'UCS-2BE');
                }, (string)$this->sym->Value);
                $this->outWrite->writeString((string)$newString);
                return 0;
            case DataTypeEnum::INT:
                $this->outWrite->writeInt((int)$this->sym->Value);
                return 0;
            case DataTypeEnum::BOOL:
                $this->outWrite->writeBool(filter_var($this->sym->Value, FILTER_VALIDATE_BOOLEAN));
                return 0;
            case DataTypeEnum::NIL:
                $this->outWrite->writeString("");
                return 0;
            default:
                //Tady se to nikdy nedostane
                return 59;
        }
    }
}