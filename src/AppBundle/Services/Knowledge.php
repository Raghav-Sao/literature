<?php

namespace AppBundle\Services;

/**
 *
 */
class Knowledge extends BaseService
{

    protected $redis;

    /**
     * @param object $logger
     * @param object $redis
     *
     * @return
     */
    public function __construct(
        $logger,
        $redis
    ) {

        parent::__construct($logger);

        $this->redis  = $redis;
    }
}
