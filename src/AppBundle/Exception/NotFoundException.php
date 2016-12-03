<?php

namespace AppBundle\Exception;

class NotFoundException extends \Exception
{
    public function __construct(
        string     $message    = '',
        array      $extra      = [],
        string     $customCode = Code::NOT_FOUND,
        int        $code       = 0,
        \Exception $previous   = null
    )
    {

        parent::__construct($message, $code, $previous);

        $this->extra      = $extra;
        $this->customCode = $customCode;
    }

    public function __toString()
    {
        return $this->message;
    }

    public function getExtra()
    {
        return $this->extra;
    }

    public function getCustomCode()
    {
        return $this->customCode;
    }
}
