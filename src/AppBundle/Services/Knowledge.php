<?php

namespace AppBundle\Services;

/**
*
*/
class Knowledge extends BaseService
{

    function __construct(
        $logger,
        $redis)
    {

        parent::__construct($logger);

        $this->redis  = $redis;
    }
}
