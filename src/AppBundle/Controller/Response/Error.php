<?php

namespace AppBundle\Controller\Response;

use Symfony\Component\HttpFoundation\JsonResponse;

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

        parent::__construct(
            [
                "success"      => false,
                // "errorCode"    => $e->getCustomCode(),
                "errorMessage" => $e->__toString(),
                // "extra"        => $e->getExtra()
            ],
            $status,
            $headers,
            $json
        );
    }
}
