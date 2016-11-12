<?php

namespace AppBundle\Services;

/**
*
*/
class Game extends BaseService
{

    function __construct(
        $logger,
        $redis,
        Services\PubSub\Interface $pubSub,
        Services\Knowledge $knowledge)
    {

        parent::__construct($logger);

        $this->redis     = $redis;
        $this->pubSub    = $pubSub;
        $this->knowledge = $knowledge;
    }
}
