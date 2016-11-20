<?php

namespace Tests\AppBundle\Service\PubSub;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

use AppBundle\Service;
use AppBundle\Model\Redis;

use AppBundle\Constant;

use AppBundle\Service\PubSub;


use Tests\AppBundle\Model\Redis\GameTestData;
use Tests\AppBundle\Model\Redis\UserTestData;


/**
 *
 */
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

        $this->knowledge = $this->createMock(Service\Knowledge::class);

		$this->gameService = new Service\Game(
	        $this->logger,
	        $this->redis,
	        $this->pubSub,
	        $this->knowledge
	    );

		// $this->logger = $this->container->get('logger');
	}

	public function testPubSubTrigger()
	{

		// $this->pubSub    = $this->createMock(Service\Mock\PubSub\Pusher::class);

		$game = new Redis\Game(GameTestData::getId(), GameTestData::getGameHash([
                                                                                    "u1" => "u_1111111111"
                                                                                ]));
		$expectedGame = new Redis\Game(GameTestData::getId(), GameTestData::getGameHash([
                                                                                    "u2" => "u_2222222222"
                                                                                ]));
		$atSN   = Constant\Game\User::USER_2;
		$userId = "u_2222222222";

		$expected  = [
            "game" => $expectedGame->toArray(), //u2 will add after join api call
            "atSN" => $atSN,
        ];

        $this->redis->method("hgetall")
                    ->willReturn(GameTestData::getGameHash());

        $this->redis->method("smembers")
                    ->will($this->onConsecutiveCalls(
                            UserTestData::getCardSet(),
                            UserTestData::getCardSet(
                                [
                                    Constant\Game\Card::CLUB_6,
                                    Constant\Game\Card::CLUB_5,
                                    Constant\Game\Card::CLUB_4,
                                    Constant\Game\Card::CLUB_3,
                                    Constant\Game\Card::CLUB_2,
                                    Constant\Game\Card::CLUB_1
                                ],
                                true
                            )
                        ));

		$this->pubSub->expects($this->once())
                 ->method('trigger')
                 ->with($this->equalTo($game->id), $this->equalTo(Constant\Game\Event::GAME_JOIN_ACTION), $this->equalTo($expected));

        $this->gameService->joinGame($game->id, $atSN, $userId);
	}
}
