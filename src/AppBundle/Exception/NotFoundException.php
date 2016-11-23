<?php

namespace AppBundle\Exception;

/**
 *
 */
class NotFoundException extends \Exception
{

    /**
     * @param string          $message
     * @param array           $extra
     * @param string          $customCode
     * @param integer         $code
     * @param \Exception|null $previous
     */
    public function __construct(
        $message = "",
        $extra = array(),
        $customCode = Code::NOT_FOUND,
        $code = 0,
        \Exception $previous = null
    ) {

        parent::__construct($message, $code, $previous);

        $this->extra      = $extra;
        $this->customCode = $customCode;
    }

    /**
     * @return string
     */
    public function __toString()
    {

        return $this->message;
    }

    /**
     * @return array
     */
    public function getExtra()
    {

        return $this->extra;
    }

    /**
     * @return string
     */
    public function getCustomCode()
    {

        return $this->customCode;
    }
}
