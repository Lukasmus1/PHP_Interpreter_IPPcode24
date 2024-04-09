<?php

namespace IPP\Student;

use IPP\Core\AbstractInterpreter;
use IPP\Core\Exception\IntegrationException;
use IPP\Student\enums\DataTypeEnum;
use IPP\Student\enums\FramesEnum;

class Interpreter extends AbstractInterpreter
{
    public function execute(): int
    {
        // Check \IPP\Core\AbstractInterpreter for predefined I/O objects:
        //$val = $this->input->readString();
        //$this->stderr->writeString("stderr");

        $dom = $this->source->getDOMDocument();
        $instructions = $dom->getElementsByTagName("instruction");

        $opcodeObj = null;
        $opcodes = [];
        foreach ($instructions as $instruction)
        {
            $opcodeObj = null;
            $opcode = $instruction->getAttribute("opcode");
            $order = $instruction->getAttribute("order");

            switch ($opcode)
            {
                case "MOVE":
                    $opcodeObj = new opcodes\MoveOC();

                    //VAR
                    $child = $instruction->getElementsByTagName("arg1");
                    list($frame, $name) = Tools::GetFrameAndName($child);
                    if ($frame == FramesEnum::ERR)
                    {
                        return 52;
                    }
                    $opcodeObj->var = new VarClass($frame, $name);

                    //SYM
                    $child = $instruction->getElementsByTagName("arg2");
                    list($type, $name) = Tools::GetTypeAndValue($child);
                    if ($type == DataTypeEnum::VAR)
                    {
                        list($frame, $name) = Tools::GetFrameAndName($child);
                        $opcodeObj->sym = new VarClass($frame, $name);
                        break;
                    }
                    else if ($type == DataTypeEnum::ERR)
                    {
                        //not sure
                        return 52;
                    }

                    $opcodeObj->sym = new SymClass($type, $name);
                    break;

                case "CREATEFRAME":
                    break;
                case "PUSHFRAME":
                    break;
                case "POPFRAME":
                    break;
                case "DEFVAR":
                    $opcodeObj = new opcodes\DefvarOC();
                    $child = $instruction->getElementsByTagName("arg1");

                    list($frame, $name) = Tools::GetFrameAndName($child);
                    if ($frame == FramesEnum::ERR)
                    {
                        return 52;
                    }

                    $opcodeObj->Sym = new VarClass($frame, $name);
                    break;

                case "CALL":
                    break;
                case "RETURN":
                    break;
                case "PUSHS":
                    break;
                case "POPS":
                    break;
                case "ADD":
                    break;
                case "SUB":
                    break;
                case "MUL":
                    break;
                case "IDIV":
                    break;
                case "LT":
                    break;
                case "GT":
                    break;
                case "EQ":
                    break;
                case "AND":
                    break;
                case "OR":
                    break;
                case "NOT":
                    break;
                case "INT2CHAR":
                    break;
                case "STR2INT":
                    break;
                case "READ":
                    break;
                case "WRITE":
                    $opcodeObj = new opcodes\WriteOC($this->stdout);
                    $child = $instruction->getElementsByTagName("arg1");

                    list($type, $name) = Tools::GetTypeAndValue($child);
                    if ($type == DataTypeEnum::VAR)
                    {
                        list($frame, $name) = Tools::GetFrameAndName($child);
                        $opcodeObj->sym = $name;

                        break;
                    }
                    else if ($type == DataTypeEnum::ERR)
                    {
                        //not sure
                        return 52;
                    }

                    $opcodeObj->sym = new SymClass($type, $name);
                    break;
            }

            if($opcodeObj != null)
                $opcodes[] = $opcodeObj;
        }

        return $this->RunOpcodes($opcodes);
    }

    private function RunOpcodes($opcodes): int
    {
        foreach ($opcodes as $opcode)
        {
            try
            {
                $opcode->Execute();
            }
            catch (IntegrationException $e)
            {
                //TODO - Tady potřebuju správný err kód
                return 1;
            }
        }

        return 0;
    }
}
