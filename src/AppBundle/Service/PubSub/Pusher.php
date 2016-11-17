<?php

namespace AppBundle\Service\PubSub;

use \Pusher as PusherClient;
use AppBundle\Service\BaseService;

/**
 *
 */
class Pusher extends BaseService implements PubSubInterface
{

    // Static factory method

    public static function create(
        bool   $mock,
               $logger,
        string $appKey,
        string $appSecret,
        string $appId)
    {

        if ($mock) {

            return new PusherMock(
                $logger
            );

        } else {

            return new Pusher(
                $logger,
                $appKey,
                $appSecret,
                $appId
            );
        }
    }



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

        $this->pusher = new PusherClient(
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
