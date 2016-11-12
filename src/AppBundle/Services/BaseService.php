<?php

namespace AppBundle\Services;

/**
*
*/
class BaseService
{

    function __construct($logger)
    {
        $this->logger = $logger;
    }
}
