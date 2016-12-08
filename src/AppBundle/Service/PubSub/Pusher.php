<?php

namespace AppBundle\Service\PubSub;

use \Pusher as PusherClient;
use AppBundle\Service\BaseService;
use AppBundle\Service\Mock\PubSub\Pusher as PusherMock;

/**
 * @codeCoverageIgnore
 */
class Pusher extends BaseService implements PubSubInterface
{
    protected $pusher;

    public static function create(
        bool   $mock,
               $logger,
        string $appKey,
        string $appSecret,
        string $appId
    )
    {
        if ($mock)
        {
            return new PusherMock(
                $logger
            );
        }
        else
        {
            return new Pusher(
                $logger,
                $appKey,
                $appSecret,
                $appId
            );
        }
    }

    public function __construct(
               $logger,
        string $appKey,
        string $appSecret,
        string $appId
    )
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

    public function trigger(
        string $channel,
        string $event,
        array  $data = []
    )
    {

        $formattedData = [
            'type' => $event,
            'data' => $data,
        ];

        try
        {
            $this->pusher->trigger($channel, $event, $formattedData);
        }
        catch (\Exception $e)
        {
            $this->logger->error($e);
        }
    }
}
