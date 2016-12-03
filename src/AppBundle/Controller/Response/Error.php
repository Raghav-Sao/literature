<?php

namespace AppBundle\Controller\Response;

use Symfony\Component\HttpFoundation\JsonResponse;

use AppBundle\Exception;

class Error extends JsonResponse
{

    public function __construct(
        \Exception $e,
        int        $status  = 200,
        array      $headers = [],
        bool       $json    = false
    )
    {
        $customCode = Exception\Code::DEFAULT;
        $extra      = [];

        if (method_exists($e, 'getCustomCode'))
        {
            $customCode = $e->getCustomCode();
        }

        if (method_exists($e, 'getExtra'))
        {
            $extra = $e->getExtra();
        }

        parent::__construct(
            [
                'success'      => false,
                'errorCode'    => $customCode,
                'errorMessage' => $e->__toString(),
                'extra'        => $extra,
            ],
            $status,
            $headers,
            $json
        );
    }
}
