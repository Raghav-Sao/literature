<?php

namespace AppBundle\Services\PubSub;

use \Pusher;
use AppBundle\Services\BaseService;

/**
*
*/
class Pusher extends BaseService implements Interface
{

    protected $pusher;

    /**
     *
     * @param object $logger
     * @param string $appKey
     * @param string $appSecret
     * @param string $appId
     *
     * @return
     */
    public function __construct(
        $logger,
        string $appKey,
        string $appSecret,
        string $appId)
    {

        parent::__construct($logger);

        $options = [
            'encrypted' => true,
        ];

        $this->pusher = new Pusher(
            $appKey,
            $appSecret,
            $appId,
            $options
        );
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
        try {

            $this->pusher->trigger($channel, $event, $data);
        } catch (\Exception $e) {

            $this->logger->error($e);
        }
    }
}
