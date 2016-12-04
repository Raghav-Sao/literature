<?php

namespace Tests\AppBundle\Service;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

use AppBundle\Service;
use AppBundle\Exception;
use AppBundle\Model\Redis;
use AppBundle\Constant\Game\Game as GameK;
use AppBundle\Constant\Game\Status;
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

    /**
     * @expectedException        \AppBundle\Exception\NotFoundException
     * @expectedExceptionMessage Cards not found for given user
     *
     */
    public function testGetUserNotFound()
    {
        $this->redis->method('smembers')
                    ->willReturn(null);

        $this->service->getUser(UserTestData::id());
    }

    /**
     * @expectedException        \AppBundle\Exception\BadRequestException
     * @expectedExceptionMessage Game with given id is no longer active
     *
     */
    public function testGetAndValidateExpiredGame()
    {
        $hash = GameTestData::hash([GameK::STATUS => Status::EXPIRED]);

        $this->redis->method('hgetall')
                    ->willReturn($hash);

        $this->service->getAndValidate(GameTestData::id(), UserTestData::id());
    }

    /**
     * @expectedException        \AppBundle\Exception\BadRequestException
     * @expectedExceptionMessage You do not belong to game with given id
     *
     */
    public function testGetAndValidateBadUser()
    {
        $hash = GameTestData::hash();

        $this->redis->method('hgetall')
                    ->willReturn($hash);

        $this->service->getAndValidate(GameTestData::id(), UserTestData::id('uid4'));
    }
}
