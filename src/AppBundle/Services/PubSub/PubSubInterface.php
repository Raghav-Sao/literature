<?php

namespace AppBundle\Services\PubSub;

/**
 *
 */
interface PubSubInterface
{

    public function trigger(string $channel, string $event, string $data);

}
