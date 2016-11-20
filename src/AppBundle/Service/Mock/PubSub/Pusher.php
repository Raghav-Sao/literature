<?php

namespace AppBundle\Service\Mock\PubSub;

use AppBundle\Service;

/**
 *
 */
class Pusher extends Service\BaseService implements Service\PubSub\PubSubInterface
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

    /**
     * @param string $channel
     * @param string $event
     * @param array  $data
     *
     * @return
     */
    public function trigger(
        string $channel,
        string $event,
        array  $data = array())
    {
    }
}
