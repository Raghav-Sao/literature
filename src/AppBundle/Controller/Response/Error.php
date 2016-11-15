<?php

namespace AppBundle\Controller\Response;

use Symfony\Component\HttpFoundation\JsonResponse;

use AppBundle\Exception;

/**
 *
 */
class Error extends JsonResponse
{
    
    public function __construct(
        \Exception $e,
                   $status  = 200,
                   $headers = array(),
                   $json    = false)
    {

        $customCode = method_exists($e, "getCustomCode") ? $e->getCustomCode() : Exception\Code::DEFAULT;
        $extra      = method_exists($e, "getExtra") ? $e->getExtra() : [];

        parent::__construct(
            [
                "success"      => false,
                "errorCode"    => $customCode,
                "errorMessage" => $e->__toString(),
                "extra"        => $extra
            ],
            $status,
            $headers,
            $json
        );
    }
}
