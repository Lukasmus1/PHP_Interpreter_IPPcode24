<?php

namespace IPP\Student\opcodes;

use IPP\Core\Exception\IntegrationException;
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

    public function Execute() : int
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
                    $this->outWrite->writeString($object->Value);
                    break;
                case DataTypeEnum::INT:
                    $this->outWrite->writeInt($object->Value);
                    break;
                case DataTypeEnum::BOOL:
                    $this->outWrite->writeBool($object->Value);
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
                $this->outWrite->writeString($this->sym->Value);
                break;
            case DataTypeEnum::INT:
                $this->outWrite->writeInt($this->sym->Value);
                break;
            case DataTypeEnum::BOOL:
                $this->outWrite->writeBool($this->sym->Value);
                break;
            case DataTypeEnum::NIL:
                $this->outWrite->writeString("");
                break;
        }
        return 0;
    }
}