<?php

namespace AppBundle\Service\PubSub;

/**
 *
 */
interface PubSubInterface
{

    public function trigger(string $channel, string $event, array $data = array());

}
