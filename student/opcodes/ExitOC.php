<?php

namespace IPP\Student\opcodes;

use IPP\Student\SymClass;
use IPP\Student\Tools;
use IPP\Student\VarClass;

class ExitOC implements IOpcodes
{
    public VarClass|SymClass $sym;
    public function Execute(int $index): int
    {
        if ($this->sym instanceof VarClass)
        {
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

        if (!is_numeric($var->Value))
        {
            return 53;
        }
        if ($var->Value < 0 || $var->Value > 9)
        {
            return 57;
        }

        //Pokud se má program ukončit s hodnotou 0, vrátí se z této metody hodnota -1, na kterou hlavní metoda v interpreteru reaguje
        if ((int)$var->Value == 0)
        {
            return -1;
        }
        return (int)$var->Value;
    }
}