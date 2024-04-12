<?php

namespace IPP\Student;

use IPP\Core\AbstractInterpreter;
use IPP\Student\enums\DataTypeEnum;
use IPP\Student\enums\FramesEnum;
use IPP\Student\opcodes\AddSubMulDivOC;
use IPP\Student\opcodes\AndOrNotOC;
use IPP\Student\opcodes\BreakOC;
use IPP\Student\opcodes\CallOC;
use IPP\Student\opcodes\ConGetSetOC;
use IPP\Student\opcodes\CreateFrameOC;
use IPP\Student\opcodes\DprintOC;
use IPP\Student\opcodes\ExitOC;
use IPP\Student\opcodes\Int2CharOC;
use IPP\Student\opcodes\IOpcodes;
use IPP\Student\opcodes\JumpIfOC;
use IPP\Student\opcodes\JumpOC;
use IPP\Student\opcodes\LabelOC;
use IPP\Student\opcodes\LtGtEqOC;
use IPP\Student\opcodes\PopFrameOC;
use IPP\Student\opcodes\PopsOC;
use IPP\Student\opcodes\PushFrame;
use IPP\Student\opcodes\PushsOC;
use IPP\Student\opcodes\ReturnOC;
use IPP\Student\opcodes\Stri2IntOC;
use IPP\Student\opcodes\StrlenOC;
use IPP\Student\opcodes\TypeOC;
use DOMElement;

class Interpreter extends AbstractInterpreter
{
    /**
     * Pomocná metoda pro získání proměnné z daného rámce
     * @param DOMElement $instruction
     * @return array{0: FramesEnum, 1: string}
     */
    private function GetVar(DOMElement $instruction) : array
    {
        $child = $instruction->getElementsByTagName("arg1");
        list($frame, $name) = Tools::GetFrameAndName($child);
        if ($frame == FramesEnum::ERR)
        {
            return [FramesEnum::ERR, "err"];
        }
        return [$frame, $name];
    }

    /**
     * Pomocná metoda pro získání "label"
     * @param DOMElement $instruction
     * @return string
     */
    private function GetLabel(DOMElement $instruction) : string
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

    /**
     * Pomocná metoda pro získání buď proměnné ze zásobníku nebo zadané konstanty
     * @param DOMElement $instruction
     * @param string $arg
     * @return array{0: DataTypeEnum|FramesEnum, 1: string}
     */
    private function GetSym(DOMElement $instruction, string $arg) : array
    {
        $child = $instruction->getElementsByTagName($arg);
        list($type, $name) = Tools::GetTypeAndValue($child);
        if ($type == DataTypeEnum::VAR)
        {
            list($frame, $name) = Tools::GetFrameAndName($child);
            return [$frame, $name];
        }
        else if ($type == DataTypeEnum::ERR)
        {
            return [DataTypeEnum::ERR, "err", "err"];
        }
        return [$type, (string)$name];
    }

    /**
     * Hlavní metoda programu
     * @throws ExceptionClass
     */
    public function execute(): int
    {
        //Získání dokumentu
        $dom = $this->source->getDOMDocument();
        $instructions = $dom->getElementsByTagName("instruction");

        $opcodes = [];
        foreach ($instructions as $instruction)
        {
            //Získání dané instrukce
            $opcodeObj = null;
            $opcode = $instruction->getAttribute("opcode");
            $order = $instruction->getAttribute("order");

            //Zpracování instrukce podle opcocde
            //Každá instrukce také získá zadané argumenty ke zpracování a vytvoří si danou instanci instrukce
            switch ($opcode)
            {
                case "MOVE":
                    $opcodeObj = new opcodes\MoveOC();

                    //VAR
                    list($frame, $name) = $this->GetVar($instruction);
                    if ($frame == FramesEnum::ERR)
                    {
                        throw new ExceptionClass(52);
                    }
                    $opcodeObj->var = new VarClass($frame, $name);

                    //SYM
                    list($arg1, $arg2) = $this->GetSym($instruction, "arg2");
                    if ($arg1 instanceof FramesEnum)
                    {
                        $opcodeObj->sym = new VarClass($arg1, $arg2);
                    }
                    else if ($arg1 instanceof DataTypeEnum && $arg1 != DataTypeEnum::ERR)
                    {
                        $opcodeObj->sym = new SymClass($arg1, $arg2);
                    }
                    else
                    {
                        throw new ExceptionClass(52);
                    }
                    break;

                case "CREATEFRAME":
                    $opcodeObj = new CreateFrameOC();
                    break;

                case "PUSHFRAME":
                    $opcodeObj = new PushFrame();
                    break;

                case "POPFRAME":
                    $opcodeObj = new PopFrameOC();
                    break;

                case "DEFVAR":
                    $opcodeObj = new opcodes\DefvarOC();

                    list($frame, $name) = $this->GetVar($instruction);
                    if ($frame == FramesEnum::ERR)
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
                    list($arg1, $arg2) = $this->GetSym($instruction, "arg1");
                                        if ($arg1 instanceof FramesEnum)
                    {
                        $opcodeObj->sym = new VarClass($arg1, $arg2);
                    }
                    else if ($arg1 instanceof DataTypeEnum && $arg1 != DataTypeEnum::ERR)
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
                    list($frame, $name) = $this->GetVar($instruction);
                    if ($frame == FramesEnum::ERR)
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
                    list($frame, $name) = $this->GetVar($instruction);
                    if ($frame == FramesEnum::ERR)
                    {
                        throw new ExceptionClass(52);
                    }
                    $opcodeObj->var = new VarClass($frame, $name);


                    //SYM1
                    list($arg1, $arg2) = $this->GetSym($instruction, "arg2");
                    if ($arg1 instanceof FramesEnum)
                    {
                        $opcodeObj->sym1 = new VarClass($arg1, $arg2);
                    }
                    else if ($arg1 instanceof DataTypeEnum && $arg1 != DataTypeEnum::ERR)
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
                    list($arg1, $arg2) = $this->GetSym($instruction, "arg3");
                    if ($arg1 instanceof FramesEnum)
                    {
                        $opcodeObj->sym2 = new VarClass($arg1, $arg2);
                    }
                    else if ($arg1 instanceof DataTypeEnum && $arg1 != DataTypeEnum::ERR)
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
                    list($frame, $name) = $this->GetVar($instruction);
                    if ($frame == FramesEnum::ERR)
                    {
                        throw new ExceptionClass(52);
                    }
                    $opcodeObj->var = new VarClass($frame, $name);

                    //SYM1
                    list($arg1, $arg2) = $this->GetSym($instruction, "arg2");
                    if ($arg1 instanceof FramesEnum)
                    {
                        $opcodeObj->sym1 = new VarClass($arg1, $arg2);
                    }
                    else if ($arg1 instanceof DataTypeEnum && $arg1 != DataTypeEnum::ERR)
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
                    list($arg1, $arg2) = $this->GetSym($instruction, "arg3");
                    if ($arg1 instanceof FramesEnum)
                    {
                        $opcodeObj->sym2 = new VarClass($arg1, $arg2);
                    }
                    else if ($arg1 instanceof DataTypeEnum && $arg1 != DataTypeEnum::ERR)
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
                    list($frame, $name) = $this->GetVar($instruction);
                    if ($frame == FramesEnum::ERR)
                    {
                        throw new ExceptionClass(52);
                    }
                    $opcodeObj->var = new VarClass($frame, $name);

                    //SYM1
                    list($arg1, $arg2) = $this->GetSym($instruction, "arg2");
                    if ($arg1 instanceof FramesEnum)
                    {
                        $opcodeObj->sym = new VarClass($arg1, $arg2);
                    }
                    else if ($arg1 instanceof DataTypeEnum && $arg1 != DataTypeEnum::ERR)
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
                    list($frame, $name) = $this->GetVar($instruction);
                    if ($frame == FramesEnum::ERR)
                    {
                        throw new ExceptionClass(52);
                    }
                    $opcodeObj->var = new VarClass($frame, $name);

                    //SYM1
                    list($arg1, $arg2) = $this->GetSym($instruction, "arg2");
                    if ($arg1 instanceof FramesEnum)
                    {
                        $opcodeObj->sym1 = new VarClass($arg1, $arg2);
                    }
                    else if ($arg1 instanceof DataTypeEnum && $arg1 != DataTypeEnum::ERR)
                    {
                        $opcodeObj->sym1 = new SymClass($arg1, $arg2);
                    }
                    else
                    {
                        throw new ExceptionClass(52);
                    }

                    //SYM2
                    list($arg1, $arg2) = $this->GetSym($instruction, "arg3");
                    if ($arg1 instanceof FramesEnum)
                    {
                        $opcodeObj->sym2 = new VarClass($arg1, $arg2);
                    }
                    else if ($arg1 instanceof DataTypeEnum && $arg1 != DataTypeEnum::ERR)
                    {
                        if (!is_numeric($arg2))
                        {
                            throw new ExceptionClass(53);
                        }
                        $opcodeObj->sym2 = new SymClass($arg1, (int)$arg2);
                    }
                    else
                    {
                        throw new ExceptionClass(52);
                    }
                    break;

                case "READ":
                    $opcodeObj = new opcodes\ReadOC($this->input);

                    //VAR
                    list($frame, $name) = $this->GetVar($instruction);
                    if ($frame == FramesEnum::ERR)
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

                    list($arg1, $arg2) = $this->GetSym($instruction, "arg1");
                    if ($arg1 instanceof FramesEnum)
                    {
                        $opcodeObj->sym = new VarClass($arg1, $arg2);
                    }
                    else if ($arg1 instanceof DataTypeEnum && $arg1 != DataTypeEnum::ERR)
                    {
                        $opcodeObj->sym = new SymClass($arg1, $arg2);
                    }
                    else
                    {
                        throw new ExceptionClass(52);
                    }
                    break;

                case "CONCAT":
                case "GETCHAR":
                case "SETCHAR":
                    $opcodeObj = new ConGetSetOC(strtolower($opcode));

                    //VAR
                    list($frame, $name) = $this->GetVar($instruction);
                    if ($frame == FramesEnum::ERR)
                    {
                        throw new ExceptionClass(52);
                    }
                    $opcodeObj->var = new VarClass($frame, $name);

                    //SYM1
                    list($arg1, $arg2) = $this->GetSym($instruction, "arg2");
                    if ($arg1 instanceof FramesEnum)
                    {
                        $opcodeObj->sym1 = new VarClass($arg1, $arg2);
                    }
                    else if ($arg1 instanceof DataTypeEnum && $arg1 != DataTypeEnum::ERR)
                    {
                        $opcodeObj->sym1 = new SymClass($arg1, $arg2);
                    }
                    else
                    {
                        throw new ExceptionClass(52);
                    }

                    //SYM2
                    list($arg1, $arg2) = $this->GetSym($instruction, "arg3");
                    if ($arg1 instanceof FramesEnum)
                    {
                        $opcodeObj->sym2 = new VarClass($arg1, $arg2);
                    }
                    else if ($arg1 instanceof DataTypeEnum && $arg1 != DataTypeEnum::ERR)
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
                    list($frame, $name) = $this->GetVar($instruction);
                    if ($frame == FramesEnum::ERR)
                    {
                        throw new ExceptionClass(52);
                    }
                    $opcodeObj->var = new VarClass($frame, $name);

                    //SYM
                    list($arg1, $arg2) = $this->GetSym($instruction, "arg2");
                    if ($arg1 instanceof FramesEnum)
                    {
                        $opcodeObj->sym = new VarClass($arg1, $arg2);
                    }
                    else if ($arg1 instanceof DataTypeEnum && $arg1 != DataTypeEnum::ERR)
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
                    list($frame, $name) = $this->GetVar($instruction);
                    if ($frame == FramesEnum::ERR)
                    {
                        throw new ExceptionClass(52);
                    }
                    $opcodeObj->var = new VarClass($frame, $name);

                    //SYM
                    list($arg1, $arg2) = $this->GetSym($instruction, "arg2");
                    if ($arg1 instanceof FramesEnum)
                    {
                        $opcodeObj->sym = new VarClass($arg1, $arg2);
                    }
                    else if ($arg1 instanceof DataTypeEnum && $arg1 != DataTypeEnum::ERR)
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

                    //LABEL
                    $opcodeObj->name = $this->GetLabel($instruction);
                    if ($opcodeObj->name == "56")
                    {
                        throw new ExceptionClass(56);
                    }
                    break;

                case "JUMP":
                    $opcodeObj = new JumpOC($opcodes);

                    //LABEL
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
                    list($arg1, $arg2) = $this->GetSym($instruction, "arg2");
                    if ($arg1 instanceof FramesEnum)
                    {
                        $opcodeObj->sym1 = new VarClass($arg1, $arg2);
                    }
                    else if ($arg1 instanceof DataTypeEnum && $arg1 != DataTypeEnum::ERR)
                    {
                        $opcodeObj->sym1 = new SymClass($arg1, $arg2);
                    }
                    else
                    {
                        throw new ExceptionClass(52);
                    }

                    //SYM2
                    list($arg1, $arg2) = $this->GetSym($instruction, "arg3");
                    if ($arg1 instanceof FramesEnum)
                    {
                        $opcodeObj->sym2 = new VarClass($arg1, $arg2);
                    }
                    else if ($arg1 instanceof DataTypeEnum && $arg1 != DataTypeEnum::ERR)
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

                    //SYM
                    list($arg1, $arg2) = $this->GetSym($instruction, "arg1");
                    if ($arg1 instanceof FramesEnum)
                    {
                        $opcodeObj->sym = new VarClass($arg1, $arg2);
                    }
                    else if ($arg1 instanceof DataTypeEnum && $arg1 != DataTypeEnum::ERR)
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
                    list($arg1, $arg2) = $this->GetSym($instruction, "arg1");
                    if ($arg1 instanceof FramesEnum)
                    {
                        $opcodeObj->sym = new VarClass($arg1, $arg2);
                    }
                    else if ($arg1 instanceof DataTypeEnum && $arg1 != DataTypeEnum::ERR)
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

            //Přiřazování instrukce do pole instrukcí podle jejího orderu
            array_splice($opcodes, (int)$order, 0, array($opcodeObj));
        }

        return $this->RunOpcodes($opcodes);
    }

    /**
     * Pomocná metoda, která zavolá metodu execute na každou instanci instrukce
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
