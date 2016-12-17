<?php

namespace AppBundle\Service\Mock;

use AppBundle\Service\BaseService;

class Redis extends BaseService
{
    public function __construct($logger)
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
