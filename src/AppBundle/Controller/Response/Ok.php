<?php

namespace AppBundle\Controller\Response;

use Symfony\Component\HttpFoundation\JsonResponse;

class Ok extends JsonResponse
{

    public function __construct(
        array $data    = null,
        int   $status  = 200,
        array $headers = [],
        bool  $json    = false
    )
    {
        parent::__construct(
            [
                'success'      => true,
                'response'     => $data,
            ],
            $status,
            $headers,
            $json
        );
    }
}
