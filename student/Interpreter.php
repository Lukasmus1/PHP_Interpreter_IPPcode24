<?php

namespace IPP\Student;

use IPP\Core\AbstractInterpreter;
use IPP\Core\Exception\IntegrationException;
use IPP\Student\enums\DataTypeEnum;
use IPP\Student\enums\FramesEnum;
use IPP\Student\opcodes\AddSubMulDivOC;
use IPP\Student\opcodes\ConGetSetOC;
use IPP\Student\opcodes\IOpcodes;
use PhpParser\Node\Stmt\Else_;
use Random\Engine\Mt19937;

class Interpreter extends AbstractInterpreter
{
    private function GetVar($instruction, string $arg) : array
    {
        $child = $instruction->getElementsByTagName($arg);
        list($frame, $name) = Tools::GetFrameAndName($child);
        if ($frame == FramesEnum::ERR)
        {
            return [null, null];
        }
        return [$frame, $name];
    }

    private function GetSym($instruction, string $arg) : array
    {
        $child = $instruction->getElementsByTagName($arg);
        list($type, $name) = Tools::GetTypeAndValue($child);
        if ($type == DataTypeEnum::VAR)
        {
            list($frame, $name) = Tools::GetFrameAndName($child);
            return [$frame, $name, "var"];
        }
        else if ($type == DataTypeEnum::ERR)
        {
            //not sure
            return [null, null, "err"];
        }
        return [$type, $name, "sym"];
    }

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
                    list($frame, $name) = $this->GetVar($instruction, "arg1");
                    if ($frame == null)
                    {
                        return 52;
                    }
                    $opcodeObj->var = new VarClass($frame, $name);

                    //SYM
                    list($arg1, $arg2, $type) = $this->GetSym($instruction, "arg2");
                    if ($type == "var")
                    {
                        $opcodeObj->sym = new VarClass($arg1, $arg2);
                    }
                    else if ($type == "sym")
                    {
                        $opcodeObj->sym = new SymClass($arg1, $arg2);
                    }
                    else
                    {
                        return 52;
                    }
                    break;

                case "CREATEFRAME":
                    break;
                case "PUSHFRAME":
                    break;
                case "POPFRAME":
                    break;
                case "DEFVAR":
                    $opcodeObj = new opcodes\DefvarOC();

                    list($frame, $name) = $this->GetVar($instruction, "arg1");
                    if ($frame == null)
                    {
                        return 52;
                    }
                    $opcodeObj->var = new VarClass($frame, $name);
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
                case "SUB":
                case "MUL":
                case "IDIV":
                    $opcodeObj = new AddSubMulDivOC(strtolower($opcode));

                    //VAR
                    list($frame, $name) = $this->GetVar($instruction, "arg1");
                    if ($frame == null)
                    {
                        return 52;
                    }
                    $opcodeObj->var = new VarClass($frame, $name);


                    //SYM1
                    list($arg1, $arg2, $type) = $this->GetSym($instruction, "arg2");
                    if ($type == "var")
                    {
                        $opcodeObj->sym1 = new VarClass($arg1, $arg2);
                    }
                    else if ($type == "sym")
                    {
                        if (!is_numeric($arg2))
                        {
                            return 53;
                        }
                        $opcodeObj->sym1 = new SymClass($arg1, (int)$arg2);
                    }
                    else
                    {
                        return 52;
                    }

                    //SYM3
                    list($arg1, $arg2, $type) = $this->GetSym($instruction, "arg3");
                    if ($type == "var")
                    {
                        $opcodeObj->sym2 = new VarClass($arg1, $arg2);
                    }
                    else if ($type == "sym")
                    {
                        if (!is_numeric($arg2))
                        {
                            return 53;
                        }
                        $opcodeObj->sym2 = new SymClass($arg1, (int)$arg2);
                    }
                    else
                    {
                        return 52;
                    }
                    break;

                case "LT":
                case "GT":
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
                case "CONCAT":
                case "GETCHAR":
                case "SETCHAR":
                    $opcodeObj = new ConGetSetOC(strtolower($opcode));

                    //VAR
                    list($frame, $name) = $this->GetVar($instruction, "arg1");
                    if ($frame == null)
                    {
                        return 52;
                    }
                    $opcodeObj->var = new VarClass($frame, $name);

                    //SYM1
                    list($arg1, $arg2, $type) = $this->GetSym($instruction, "arg2");
                    if ($type == "var")
                    {
                        $opcodeObj->sym1 = new VarClass($arg1, $arg2);
                    }
                    else if ($type == "sym")
                    {
                        $opcodeObj->sym1 = new SymClass($arg1, $arg2);
                    }
                    else
                    {
                        return 52;
                    }

                    //SYM2
                    list($arg1, $arg2, $type) = $this->GetSym($instruction, "arg3");
                    if ($type == "var")
                    {
                        $opcodeObj->sym2 = new VarClass($arg1, $arg2);
                    }
                    else if ($type == "sym")
                    {
                        $opcodeObj->sym2 = new SymClass($arg1, $arg2);
                    }
                    else
                    {
                        return 52;
                    }

            }

            if($opcodeObj != null)
                $opcodes[] = $opcodeObj;
        }

        return $this->RunOpcodes($opcodes);
    }

    /**
     * @param IOpcodes[] $opcodes
     */
    private function RunOpcodes(array $opcodes): int
    {
        foreach ($opcodes as $opcode)
        {
            $ret = $opcode->Execute();
            if ($ret != 0)
            {
                return $ret;
            }
        }

        return 0;
    }
}
