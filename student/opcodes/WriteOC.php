<?php

namespace IPP\Student\opcodes;

use IPP\Core\Interface\OutputWriter;
use IPP\Core\StreamWriter;
use IPP\Student\enums\DataTypeEnum;
use IPP\Student\opcodes\IOpcodes;
use IPP\Student\SymClass;
use IPP\Student\Tools;
use IPP\Student\VarClass;

class WriteOC implements IOpcodes
{
    public string|SymClass $sym;
    private OutputWriter $outWrite;

    public function __construct(OutputWriter $out)
    {
        $this->outWrite = $out;
        $sym = null;
    }

    public function Execute(int $index) : int
    {
        if (is_string($this->sym))
        {
            $object = Tools::FindInFrame($this->sym);
            if ($object == null)
            {
                return 54;
            }

            switch ($object->Type)
            {
                case DataTypeEnum::STRING:
                    $newString = preg_replace_callback('/\\\\(\d{3})/', function ($matches) {
                        return mb_convert_encoding(pack('n', $matches[1]), 'UTF-8', 'UCS-2BE');
                    }, (string)$object->Value);
                    $this->outWrite->writeString($newString);
                    break;
                case DataTypeEnum::INT:
                    $this->outWrite->writeInt($object->Value);
                    break;
                case DataTypeEnum::BOOL:
                    $this->outWrite->writeBool(filter_var($object->Value, FILTER_VALIDATE_BOOLEAN));
                    break;
                case DataTypeEnum::NIL:
                    $this->outWrite->writeString("");
                    break;
            }
            return 0;
        }

        switch ($this->sym->Type)
        {
            case DataTypeEnum::STRING:
                $newString = preg_replace_callback('/\\\\(\d{3})/', function ($matches) {
                    return mb_convert_encoding(pack('n', $matches[1]), 'UTF-8', 'UCS-2BE');
                }, (string)$this->sym->Value);
                $this->outWrite->writeString($newString);
                break;
            case DataTypeEnum::INT:
                $this->outWrite->writeInt($this->sym->Value);
                break;
            case DataTypeEnum::BOOL:
                $this->outWrite->writeBool(filter_var($this->sym->Value, FILTER_VALIDATE_BOOLEAN));
                break;
            case DataTypeEnum::NIL:
                $this->outWrite->writeString("");
                break;
        }
        return 0;
    }
}