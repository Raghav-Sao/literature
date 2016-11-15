<?php

namespace AppBundle\Exception;


/**
 *
 */
class BadRequestException extends \Exception
{

    function __construct(
        string    $message    = "",
        array     $extra      = array(),
        string    $customCode = Code::BAD_REQUEST,
        int       $code       = 0,
        \Exception $previous   = null)
    {

        parent::__construct($message, $code, $previous);

        $this->extra      = $extra;
        $this->customCode = $customCode;
    }

    public function __toString() {

        return $this->message;
    }

    public function getExtra() {

        return $this->extra;
    }

    public function getCustomCode() {

        return $this->customCode;
    }
}
