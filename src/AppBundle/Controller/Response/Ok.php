<?php

namespace AppBundle\Controller\Response;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 *
 */
class Ok extends JsonResponse
{

    /**
     * @param [type]  $data
     * @param integer $status
     * @param array   $headers
     * @param boolean $json
     */
    public function __construct(
        $data = null,
        $status = 200,
        $headers = array(),
        $json = false
    ) {

        parent::__construct(
            [
                "success"  => true,
                "response" => $data,
            ],
            $status,
            $headers,
            $json
        );
    }
}
