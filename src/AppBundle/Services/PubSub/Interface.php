<?php

namespace AppBundle\Services\PubSub;

/**
 *
 */
interface Interface
{

    public function trigger(
        string $channel,
        string $event,
        string $data);

}
