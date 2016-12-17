<?php

namespace AppBundle\Service;

class BaseService
{
    protected $logger;

    public function __construct($logger)
    {
        $this->logger = $logger;
    }
}
