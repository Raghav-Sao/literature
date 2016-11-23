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
        $logger
    ) {

        parent::__construct($logger);
    }

    /**
     * @return
     */
    public function hgetall()
    {
    }

    /**
     * @return
     */
    public function del()
    {
    }

    /**
     * @return
     */
    public function smembers()
    {
    }

    /**
     * @return
     */
    public function hmset()
    {
    }

    /**
     * @return
     */
    public function sadd()
    {
    }

    /**
     * @return
     */
    public function smove()
    {
    }
}
