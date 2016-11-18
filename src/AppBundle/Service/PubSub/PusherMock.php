<?php

namespace AppBundle\Service\PubSub;

use AppBundle\Service\BaseService;

/**
 *
 */
class PusherMock extends BaseService implements PubSubInterface
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
     * @param string $data
     *
     * @return
     */
    public function trigger(
        string $channel,
        string $event,
        string $data)
    {
    }
}
