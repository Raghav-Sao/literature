<?php

namespace AppBundle\Exceptions;

use \Exception;

/**
 *
 */
class BadRequestException extends Exception
{

    function __construct(
        string $message,
        int $code = 0,
        Exception $previous = null)
    {

        parent::__construct($message, $code, $previous);
    }

    public function __toString() {

        return sprintf("%s: %s", __CLASS__, $this->message);
    }
}
