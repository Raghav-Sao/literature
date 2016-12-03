<?php

namespace AppBundle\Service\Mock\PubSub;

use AppBundle\Service\BaseService;
use AppBundle\Service\PubSub\PubSubInterface;

class Pusher extends BaseService implements PubSubInterface
{
    public function __construct(
        $logger
    )
    {
        parent::__construct($logger);
    }

    public function trigger(
        string $channel,
        string $event,
        array  $data = []
    )
    {
    }
}
