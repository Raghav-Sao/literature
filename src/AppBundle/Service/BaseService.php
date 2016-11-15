<?php

namespace AppBundle\Service;

/**
*
*/
class BaseService
{

    protected $logger;

    /**
     * @param object $logger
     *
     * @return
     */
    public function __construct($logger)
    {
        $this->logger = $logger;
    }
}
