<?php


namespace BbOrm\Exceptions;


class UnknownPropertyException extends \Exception
{


    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct("Tried to access non-existing property '$message'.", $code, $previous);
    }

}