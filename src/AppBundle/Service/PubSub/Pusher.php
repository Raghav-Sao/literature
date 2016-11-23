<?php

namespace AppBundle\Service\PubSub;

use \Pusher as PusherClient;
use AppBundle\Service;

/**
 *
 */
class Pusher extends Service\BaseService implements PubSubInterface
{
    protected $pusher;

    /**
     * @param  bool   $mock
     * @param  [type] $logger
     * @param  string $appKey
     * @param  string $appSecret
     * @param  string $appId
     * @return Service\Mock\PubSub\Pusher|Pusher
     */
    public static function create(
        bool   $mock,
        $logger,
        string $appKey,
        string $appSecret,
        string $appId
    ) {

        if ($mock) {
            return new Service\Mock\PubSub\Pusher(
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
        string $appId
    ) {

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
     * @param array  $data
     *
     * @return
     */
    public function trigger(
        string $channel,
        string $event,
        array $data = array()
    ) {

        $formattedData = [
            "eventType" => $event,
            "eventData" => $data,
        ];

        try {
            $this->pusher->trigger($channel, $event, $formattedData);
        } catch (\Exception $e) {
            $this->logger->error($e);
        }
    }
}
