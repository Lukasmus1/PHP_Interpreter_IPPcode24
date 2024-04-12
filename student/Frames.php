<?php

namespace IPP\Student;

//Statická třída pro uchovávání stavu rámců
class Frames
{
    /**
     * Globální rámec
     * @var VarClass[]
     */
    public static array $GlobalFrame = [];

    /**
     * Lokální rámec
     * @var TempInstance[]
     */
    public static array $LocalFrame = [];
    public static bool $localInitialized = false;

    /**
     * Zásobník
     * @var VarClass[]|SymClass[]
     */
    public static array $Frame = [];
}