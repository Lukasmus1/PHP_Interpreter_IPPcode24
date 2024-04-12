<?php

namespace IPP\Student;

use IPP\Core\Exception\IPPException;
use Throwable;

//Třída pro házení výjimek
class ExceptionClass extends IPPException
{
    public function __construct(int $code, string $message = "Chybička :koteseni:", ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous, false);
    }
}