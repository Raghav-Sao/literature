<?php

namespace AppBundle\Controller\Response;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 *
 */
class Ok extends JsonResponse
{
    
    public function __construct(
        $data    = null,
        $status  = 200,
        $headers = array(),
        $json    = false)
    {   

        parent::__construct(
            [
                "success"  => true,
                "response" => $data
            ],
            $status,
            $headers,
            $json
        );
    }
}
