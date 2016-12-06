<?php

namespace Tests\AppBundle\Service;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

use AppBundle\Service;
use AppBundle\Model\Redis\Game;
use AppBundle\Constant\Game\Game as GameK;
use AppBundle\Constant\Game\Status;
use AppBundle\Constant\Game\Card;

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

    /**
     * @expectedException        \AppBundle\Exception\BadRequestException
     * @expectedExceptionMessage Invalid position to join as member
     *
     */
    public function testJoinInvalidPosition()
    {
        $hash = GameTestData::hash();

        $this->redis->method('hgetall')
                    ->willReturn($hash);

        $this->service->join(GameTestData::id(), GameK::U1, UserTestData::id());
    }

    public function testJoin()
    {
        $id   = GameTestData::id();

        $hash = GameTestData::hash([
            GameK::U2       => null,
        ]);

        $this->redis->method('hgetall')
                    ->willReturn($hash);

        $this->redis->expects($this->once())
                    ->method('hmset')
                    ->with(
                        $id,

                        GameK::U2,
                        'uid2',

                        GameK::STATUS,
                        Status::INITIALIZED,

                        GameK::PREV_TURN_TIMESTAMP,
                        null
                    );

        $this->redis->expects($this->once())
                    ->method('sadd')
                    ->with(
                        'uid2',
                        'c4',
                        'c5',
                        'c6',
                        's4',
                        's5',
                        's6',
                        'd4',
                        'd5',
                        'd6',
                        'h4',
                        'h5',
                        'h6'
                    );

        $this->redis->method('smembers')
                    ->willReturn([
                        'c4',
                        'c5',
                        'c6',
                        's4',
                        's5',
                        's6',
                        'd4',
                        'd5',
                        'd6',
                        'h4',
                        'h5',
                        'h6'
                    ]);

        list($game, $user) = $this->service->join($id, GameK::U2, 'uid2');
    }

    public function testJoinAllAndValidateHash()
    {
        $id   = GameTestData::id();
        $hash = GameTestData::hash([
            'u4_cards' => 'c11,c12,c13'             // Small set for testing
        ]);

        $this->redis->method('hgetall')
                    ->willReturn($hash);

        $this->redis->expects($this->once())
                    ->method('hmset')
                    ->with(
                        $id,

                        GameK::U4,
                        'uid4',

                        GameK::STATUS,
                        Status::ACTIVE,             /* << */

                        GameK::PREV_TURN_TIMESTAMP,
                        $this->isType('int')        /* << */
                    );

        $this->redis->expects($this->once())
                    ->method('sadd')
                    ->with(
                        'uid4',
                        'c11',
                        'c12',
                        'c13'
                    );

        $this->redis->method('smembers')
                    ->willReturn([
                        'c11',
                        'c12',
                        'c13'
                    ]);

        list($game, $user) = $this->service->join($id, GameK::U4, 'uid4');
    }

        // Mock required in move endpoint (At most):
        // - smembers: 2
        // - smove:    1
        // - hmset:    1

        // - Pusher.trigger

    /**
     * @expectedException        \AppBundle\Exception\BadRequestException
     * @expectedExceptionMessage Game is not active
     *
     */
    public function testMoveInactiveGame()
    {
        $id   = GameTestData::id();
        $hash = GameTestData::hash();
        $game = new Game($id, $hash);

        $card = Card::CLUB_4;

        $toUserId    = UserTestData::id();
        $toUserSet   = UserTestData::set();

        $fromUserId  = UserTestData::id('uid2');
        $fromUserSet = UserTestData::set([Card::CLUB_4, Card::CLUB_5], true);

        $consecutiveReturns = $this->onConsecutiveCalls(
                                        $toUserSet,
                                        $fromUserSet
                                    );
        $this->redis->method('smembers')->will($consecutiveReturns);

        list($success, $game, $toUser) = $this->service
                                              ->moveCard(
                                                    $game,
                                                    $card,
                                                    $fromUserId,
                                                    $toUserId
                                                );
    }

    public function testMoveInvalidCard()
    {
    }

    public function testMoveInvalidFromUser()
    {
    }

    public function testMoveInvalidTurn()
    {
    }

    public function testMoveBadFromUser()
    {
        // From and To users are team
    }

    public function testMoveBadCard()
    {
        // You already have that card
    }

    public function testMoveBadCardAgain()
    {
        // You dont' have cards of that type and range
    }

    public function testMoveSuccess()
    {
    }

    public function testMoveFail()
    {
    }


}
