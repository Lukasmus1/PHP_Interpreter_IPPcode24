<?php

namespace IPP\Student;

//Třída pro vytváření dočasného rámce
//Tato třída existuje abych se mohl ukládat statický dočasný rámec jako instance do lokálního rámce
class TempInstance
{
    /**
     * Dočásný rámec pro instanci do lokálního rámce
     * @var VarClass[]|null[]
     */
    public array $TempFrame = [];
}