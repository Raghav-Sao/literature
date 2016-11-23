<?php

namespace AppBundle\Service\PubSub;

/**
 *
 */
interface PubSubInterface
{

    /**
     * @param  string $channel
     * @param  string $event
     * @param  array  $data
     * @return
     */
    public function trigger(string $channel, string $event, array $data = array());
}
