<?php

namespace IPP\Student;

use _PHPStan_11268e5ee\Nette\PhpGenerator\Type;
use IPP\Core\AbstractInterpreter;
use IPP\Student\enums\DataTypeEnum;
use IPP\Student\enums\FramesEnum;
use IPP\Student\opcodes\AddSubMulDivOC;
use IPP\Student\opcodes\AndOrNotOC;
use IPP\Student\opcodes\BreakOC;
use IPP\Student\opcodes\CallOC;
use IPP\Student\opcodes\ConGetSetOC;
use IPP\Student\opcodes\DprintOC;
use IPP\Student\opcodes\ExitOC;
use IPP\Student\opcodes\Int2CharOC;
use IPP\Student\opcodes\IOpcodes;
use IPP\Student\opcodes\JumpIfOC;
use IPP\Student\opcodes\JumpOC;
use IPP\Student\opcodes\LabelOC;
use IPP\Student\opcodes\LtGtEqOC;
use IPP\Student\opcodes\PopsOC;
use IPP\Student\opcodes\PushsOC;
use IPP\Student\opcodes\ReturnOC;
use IPP\Student\opcodes\Stri2IntOC;
use IPP\Student\opcodes\StrlenOC;
use IPP\Student\opcodes\TypeOC;
use PHPStan\Rules\Functions\ReturnNullsafeByRefRule;

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

    private function GetLabel($instruction) : string
    {
        $child = $instruction->getElementsByTagName("arg1")->item(0);
        if ($child == null)
        {
            return "56";
        }

        if ($child->nodeValue == null)
        {
            return "56";
        }

        return $child->nodeValue;
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
                        throw new ExceptionClass(52);
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
                        throw new ExceptionClass(52);
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
                        throw new ExceptionClass(52);
                    }
                    $opcodeObj->var = new VarClass($frame, $name);
                    break;

                case "CALL":
                    $opcodeObj = new CallOC($opcodes);

                    $opcodeObj->label = $this->GetLabel($instruction);
                    if ($opcodeObj->label == "56")
                    {
                        throw new ExceptionClass(56);
                    }
                    break;

                case "RETURN":
                    $opcodeObj = new ReturnOC();
                    break;

                case "PUSHS":
                    $opcodeObj = new PushsOC();

                    //SYM
                    list($arg1, $arg2, $type) = $this->GetSym($instruction, "arg1");
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
                        throw new ExceptionClass(52);
                    }
                    break;

                case "POPS":
                    $opcodeObj = new PopsOC();

                    //VAR
                    list($frame, $name) = $this->GetVar($instruction, "arg1");
                    if ($frame == null)
                    {
                        throw new ExceptionClass(52);
                    }
                    $opcodeObj->var = new VarClass($frame, $name);
                    break;

                case "ADD":
                case "SUB":
                case "MUL":
                case "IDIV":
                case "LT":
                case "GT":
                case "EQ":
                    if ($opcode == "LT" || $opcode == "GT" || $opcode == "EQ")
                    {
                        $opcodeObj = new LtGtEqOC(strtolower($opcode));
                    }
                    else
                    {
                        $opcodeObj = new AddSubMulDivOC(strtolower($opcode));
                    }

                    //VAR
                    list($frame, $name) = $this->GetVar($instruction, "arg1");
                    if ($frame == null)
                    {
                        throw new ExceptionClass(52);
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
                        if (!is_numeric($arg2) && ($opcode == "ADD" || $opcode == "SUB" || $opcode == "MUL" || $opcode == "IDIV"))
                        {
                            throw new ExceptionClass(53);
                        }
                        $opcodeObj->sym1 = new SymClass($arg1, $arg2);
                    }
                    else
                    {
                        throw new ExceptionClass(52);
                    }

                    //SYM3
                    list($arg1, $arg2, $type) = $this->GetSym($instruction, "arg3");
                    if ($type == "var")
                    {
                        $opcodeObj->sym2 = new VarClass($arg1, $arg2);
                    }
                    else if ($type == "sym")
                    {
                        if (!is_numeric($arg2) && ($opcode == "ADD" || $opcode == "SUB" || $opcode == "MUL" || $opcode == "IDIV"))
                        {
                            throw new ExceptionClass(53);
                        }
                        $opcodeObj->sym2 = new SymClass($arg1, $arg2);
                    }
                    else
                    {
                        throw new ExceptionClass(52);
                    }
                    break;

                case "AND":
                case "OR":
                case "NOT":
                    $opcodeObj = new AndOrNotOC(strtolower($opcode));

                    //VAR
                    list($frame, $name) = $this->GetVar($instruction, "arg1");
                    if ($frame == null)
                    {
                        throw new ExceptionClass(52);
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
                        throw new ExceptionClass(52);
                    }

                    if ($opcode == "NOT")
                    {
                        $opcodeObj->sym2 = new SymClass(DataTypeEnum::BOOL, "true");
                        break;
                    }

                    //SYM3
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
                        throw new ExceptionClass(52);
                    }
                    break;

                case "INT2CHAR":
                    $opcodeObj = new Int2CharOC();

                    //VAR
                    list($frame, $name) = $this->GetVar($instruction, "arg1");
                    if ($frame == null)
                    {
                        throw new ExceptionClass(52);
                    }
                    $opcodeObj->var = new VarClass($frame, $name);

                    //SYM1
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
                        throw new ExceptionClass(52);
                    }
                    break;

                case "STRI2INT":
                    $opcodeObj = new Stri2IntOC();

                    //VAR
                    list($frame, $name) = $this->GetVar($instruction, "arg1");
                    if ($frame == null)
                    {
                        throw new ExceptionClass(52);
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
                        throw new ExceptionClass(52);
                    }

                    //SYM2
                    list($arg1, $arg2, $type) = $this->GetSym($instruction, "arg3");
                    if ($type == "var")
                    {
                        $opcodeObj->sym2 = new VarClass($arg1, $arg2);
                    }
                    else if ($type == "sym")
                    {
                        if (!is_numeric($arg2))
                        {
                            throw new ExceptionClass(53);
                        }
                        $opcodeObj->sym2 = new SymClass($arg1, $arg2);
                    }
                    else
                    {
                        throw new ExceptionClass(52);
                    }
                    break;

                case "READ":
                    $opcodeObj = new opcodes\ReadOC($this->input);

                    //VAR
                    list($frame, $name) = $this->GetVar($instruction, "arg1");
                    if ($frame == null)
                    {
                        throw new ExceptionClass(52);
                    }
                    $opcodeObj->var = new VarClass($frame, $name);

                    $child = $instruction->getElementsByTagName("arg2")->item(0);
                    if ($child == null)
                    {
                        throw new ExceptionClass(56);
                    }
                    $type = $child->getAttribute("type");
                    $types = ["int", "string", "bool"];

                    if ($type != "type")
                    {
                        throw new ExceptionClass(32);
                    }

                    if (!in_array($child->nodeValue, $types))
                    {
                        throw new ExceptionClass(53);
                    }

                    $opcodeObj->type = (string)$child->nodeValue;
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
                        throw new ExceptionClass(52);
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
                        throw new ExceptionClass(52);
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
                        throw new ExceptionClass(52);
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
                        throw new ExceptionClass(52);
                    }
                    break;

                case "STRLEN":
                    $opcodeObj = new StrlenOC();

                    //VAR
                    list($frame, $name) = $this->GetVar($instruction, "arg1");
                    if ($frame == null)
                    {
                        throw new ExceptionClass(52);
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
                        throw new ExceptionClass(52);
                    }
                    break;

                case "TYPE":
                    $opcodeObj = new TypeOC();

                    //VAR
                    list($frame, $name) = $this->GetVar($instruction, "arg1");
                    if ($frame == null)
                    {
                        throw new ExceptionClass(52);
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
                        throw new ExceptionClass(52);
                    }
                    break;

                case "LABEL":
                    $opcodeObj = new LabelOC($opcodes);

                    $opcodeObj->name = $this->GetLabel($instruction);
                    if ($opcodeObj->name == "56")
                    {
                        throw new ExceptionClass(56);
                    }
                    break;

                case "JUMP":
                    $opcodeObj = new JumpOC($opcodes);

                    $opcodeObj->name = $this->GetLabel($instruction);
                    if ($opcodeObj->name == "56")
                    {
                        throw new ExceptionClass(56);
                    }
                    break;

                case "JUMPIFEQ":
                case "JUMPIFNEQ":
                    if ($opcode == "JUMPIFEQ")
                    {
                        $opcodeObj = new JumpIfOC($opcodes, "eq");
                    }
                    else
                    {
                        $opcodeObj = new JumpIfOC($opcodes, "neq");
                    }

                    $opcodeObj->name = $this->GetLabel($instruction);
                    if ($opcodeObj->name == "56")
                    {
                        throw new ExceptionClass(56);
                    }

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
                        throw new ExceptionClass(52);
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
                        throw new ExceptionClass(52);
                    }
                    break;

                case "EXIT":
                    $opcodeObj = new ExitOC();

                    list($arg1, $arg2, $type) = $this->GetSym($instruction, "arg1");
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
                        throw new ExceptionClass(52);
                    }
                    break;

                case "DPRINT":
                    $opcodeObj = new DprintOC($this->stderr);

                    //SYM
                    list($arg1, $arg2, $type) = $this->GetSym($instruction, "arg1");
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
                        throw new ExceptionClass(52);
                    }
                    break;

                case "BREAK":
                    $opcodeObj = new BreakOC($this->stderr);
                    break;
            }

            if($opcodeObj != null)
                $opcodes[] = $opcodeObj;
        }

        return $this->RunOpcodes($opcodes);
    }

    /**
     * @param IOpcodes[] $opcodes
     * @throws ExceptionClass
     */
    private function RunOpcodes(array $opcodes): int
    {
        for ($i = 0; $i < count($opcodes); $i++)
        {
            $opcode = $opcodes[$i];
            $ret = $opcode->Execute($i);
            if ($ret == -2)
            {
                throw new ExceptionClass(56);
            }
            if ($ret == -1)
            {
                return 0;
            }
            if ($ret != 0)
            {
                throw new ExceptionClass($ret);
            }
        }
        return 0;
    }
}
