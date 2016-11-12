<?php

namespace AppBundle\Services\PubSub;

use \Pusher;
use AppBundle\Services\BaseService;

/**
*
*/
class Pusher extends BaseService implements Interface
{

    function __construct(
        $logger,
        string $appKey,
        string $appSecret,
        string $appId)
    {

        parent::__construct($logger);

        $options = [
            'encrypted' => true
        ];

        $this->pusher = new Pusher(
            $appKey,
            $appSecret,
            $appId,
            $options
        );
    }

    public function trigger(
        string $channel,
        string $event,
        string $data)
    {
        try {

            $this->pusher->trigger($channel, $event, $data);
        } catch (\Exception $e) {

            $this->logger->error($e);
        }
    }
}
