<?php


namespace BbOrm\Exceptions;


use Throwable;

class AccessViolationException extends \Exception
{

    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct("Tried to access private (or protected) property '$message'.", $code, $previous);
    }

}