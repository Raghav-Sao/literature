<?php

namespace AppBundle\Service;

class KnowledgeService extends BaseService
{
    protected $redis;

    public function __construct($logger, $redis)
    {
        parent::__construct($logger);

        $this->redis  = $redis;
    }
}
