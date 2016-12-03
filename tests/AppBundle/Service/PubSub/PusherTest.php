<?php

namespace Tests\AppBundle\Service\PubSub;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

use AppBundle\Service;
use AppBundle\Model\Redis;
use AppBundle\Constant\Game\Card;
use AppBundle\Constant\Game\Event;
use AppBundle\Constant\Game\Game as GameK;
use AppBundle\Service\PubSub;
use Tests\AppBundle\Model\Redis\GameTestData;
use Tests\AppBundle\Model\Redis\UserTestData;

class PusherTest extends KernelTestCase
{
    private $container;

    private $logger;
    private $redis;
    private $pubSub;
    private $knowledge;

    private $gameService;

    protected function setUp()
    {
        self::bootKernel();

        $this->container = static::$kernel->getContainer();

        $this->logger    = $this->container->get('logger');
        $this->redis     = $this->createMock(Service\Mock\Redis::class);
        $this->pubSub    = $this->createMock(Service\Mock\PubSub\Pusher::class);
        $this->knowledge = $this->createMock(Service\KnowledgeService::class);

        $this->gameService = new Service\gameService(
            $this->logger,
            $this->redis,
            $this->pubSub,
            $this->knowledge
        );
    }

    public function testPubSubTrigger()
    {
        $game = new Redis\Game(GameTestData::getId(), GameTestData::getGameHash());

        $expectedGame = new Redis\Game(
            GameTestData::getId(),
            GameTestData::getGameHash(['u2' => 'u_2222222222'])
        );

        $atSN   = GameK::U2;
        $userId = 'u_2222222222';

        $expected  = [
            'game' => $expectedGame->toArray(), // u2 will get added after join api call
            'atSN' => $atSN,
        ];

        $this->redis->method('hgetall')
                    ->willReturn(GameTestData::getGameHash());

        $this->redis->method('smembers')
                    ->will(
                        $this->onConsecutiveCalls(
                            UserTestData::getCardSet(),
                            UserTestData::getCardSet(
                                [
                                    Card::CLUB_6,
                                    Card::CLUB_5,
                                    Card::CLUB_4,
                                    Card::CLUB_3,
                                    Card::CLUB_2,
                                    Card::CLUB_1,
                                ],
                                true
                            )
                        )
                    );

        $this->pubSub->expects($this->once())
                     ->method('trigger')
                     ->with(
                         $this->equalTo($game->id),
                         $this->equalTo(Event::GAME_JOIN_ACTION),
                         $this->equalTo($expected)
                     );

        $this->gameService->join($game->id, $atSN, $userId);
    }
}
