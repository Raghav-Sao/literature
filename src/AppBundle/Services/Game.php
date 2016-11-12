<?php

namespace AppBundle\Services;

/**
 *
 */
class Game extends BaseService
{

    protected $redis;
    protected $pubSub;
    protected $knowledge;

    /**
     * @param object                    $logger
     * @param object                    $redis
     * @param Services\PubSub\Interface $pubSub
     * @param Services\Knowledge        $knowledge
     *
     * @return
     */
    public function __construct(
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
