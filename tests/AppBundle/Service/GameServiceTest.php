<?php

namespace Tests\AppBundle\Service;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

use AppBundle\Service;
use AppBundle\Model\Redis\Game;
use AppBundle\Constant\Game\Status;
use AppBundle\Constant\Game\Card;
use AppBundle\Constant\Game\Event;

use Tests\AppBundle\Model\Redis\GameTestData;
use Tests\AppBundle\Model\Redis\UserTestData;

class GameTest extends KernelTestCase
{

    private $container;

    private $logger;
    private $redis;
    private $pubSub;
    private $knowledge;

    private $service;

    protected function setUp()
    {
        self::bootKernel();

        $this->container = static::$kernel->getContainer();

        $this->logger    = $this->container->get('logger');

        //
        // TODO:
        // - Do not mock redis & knowledge service.
        //   Test by making redis calls to ensure things are happening.
        //   Mock only things which not operable on local.
        // - Do not put exceptions expecations as phpunit annotations, use the method call.
        //

        $this->redis     = $this->createMock(Service\Mock\Redis::class);
        $this->pubSub    = $this->createMock(Service\Mock\PubSub\Pusher::class);
        $this->knowledge = $this->createMock(Service\KnowledgeService::class);

        $this->service   = new Service\GameService(
            $this->logger,
            $this->redis,
            $this->pubSub,
            $this->knowledge
        );
    }
}
