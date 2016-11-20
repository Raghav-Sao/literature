<?php

namespace AppBundle\Service\Mock;

use AppBundle\Service;

/**
 *
 */
class Redis extends Service\BaseService
{
    /**
     *
     * @param object $logger
     *
     * @return
     */
    public function __construct(
        $logger)
    {
        parent::__construct($logger);
    }

    public function hgetall()
    {
    }

    public function del()
    {
    }

    public function smembers()
    {
    }

    public function hmset()
    {
    }

    public function sadd()
    {
    }

    public function smove()
    {
    }
}
