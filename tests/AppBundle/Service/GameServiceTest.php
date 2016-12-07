<?php

namespace Tests\AppBundle\Service;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

use AppBundle\Service;
use AppBundle\Model\Redis\Game;
use AppBundle\Constant\Game\Game as GameK;
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

    /**
     * @expectedException        \AppBundle\Exception\BadRequestException
     * @expectedExceptionMessage Not a valid card
     *
     */
    public function testMoveInvalidCard()
    {
        $id   = GameTestData::id();
        $hash = GameTestData::activeHash();
        $game = new Game($id, $hash);

        $card = 'c100';

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

    /**
     * @expectedException        \AppBundle\Exception\BadRequestException
     * @expectedExceptionMessage Bad value for fromUserId
     *
     */
    public function testMoveInvalidFromUser()
    {
        $id   = GameTestData::id();
        $hash = GameTestData::activeHash();
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
                                                    'uid5',
                                                    $toUserId
                                                );
    }

    /**
     * @expectedException        \AppBundle\Exception\BadRequestException
     * @expectedExceptionMessage It is not your turn to make a move
     *
     */
    public function testMoveInvalidTurn()
    {
        $id   = GameTestData::id();
        $hash = GameTestData::activeHash([
            'next_turn' => 'u3',
        ]);
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

    /**
     * @expectedException        \AppBundle\Exception\BadRequestException
     * @expectedExceptionMessage Bad value for fromUserId, You are partners
     *
     */
    public function testMoveBadFromUser()
    {
        // From and To users are team

        $id   = GameTestData::id();
        $hash = GameTestData::activeHash();
        $game = new Game($id, $hash);

        $card = Card::CLUB_4;

        $toUserId    = UserTestData::id();
        $toUserSet   = UserTestData::set();

        $fromUserId  = UserTestData::id('uid3');
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

    /**
     * @expectedException        \AppBundle\Exception\BadRequestException
     * @expectedExceptionMessage Bad value for card, You have it already
     *
     */
    public function testMoveBadCard()
    {
        // You already have that card

        $id   = GameTestData::id();
        $hash = GameTestData::activeHash();
        $game = new Game($id, $hash);

        $card = Card::CLUB_1;

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

    /**
     * @expectedException        \AppBundle\Exception\BadRequestException
     * @expectedExceptionMessage You do not have at least one card of that type. Invalid move
     *
     */
    public function testMoveBadCardAgain()
    {
        // You dont' have cards of that type and range

        $id   = GameTestData::id();
        $hash = GameTestData::activeHash();
        $game = new Game($id, $hash);

        $card = Card::CLUB_8;

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

    public function testMoveSuccess()
    {
        $id   = GameTestData::id();
        $hash = GameTestData::activeHash();
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

        $this->redis->expects($this->once())
                    ->method('smove')
                    ->with(
                        $fromUserId,
                        $toUserId,
                        $card
                    );

        $this->redis->expects($this->once())
                    ->method('hmset')
                    ->with(
                        $id,

                        GameK::PREV_TURN,
                        'u1',

                        GameK::PREV_TURN_TIMESTAMP,
                        $this->isType('int'),

                        GameK::NEXT_TURN,
                        'u1'
                    );

        $this->pubSub->expects($this->once())
                     ->method('trigger')
                     ->with(
                        $id,
                        Event::GAME_MOVE_ACTION,
                        $this->callback(function($payload) {

                            $isSuccess = ($payload['success'] === true);
                            $hasGame   = isset($payload['game']);

                            return $isSuccess && $hasGame;
                        })
                     );

        $now = microtime(true);

        list($success, $game, $toUser) = $this->service
                                              ->moveCard(
                                                    $game,
                                                    $card,
                                                    $fromUserId,
                                                    $toUserId
                                                );

        $this->assertTrue($success);

        $this->assertEquals('u1', $game->prevTurn);
        $this->assertEquals('u1', $game->nextTurn);
        $this->assertLessThan($now, $game->prevTurnTime);

        $this->assertCount(13, $toUser->cards);
        $this->assertContains(Card::CLUB_4, $toUser->cards);
    }

    public function testMoveFail()
    {
        $id   = GameTestData::id();
        $hash = GameTestData::activeHash();
        $game = new Game($id, $hash);

        $card = Card::CLUB_6;

        $toUserId    = UserTestData::id();
        $toUserSet   = UserTestData::set();

        $fromUserId  = UserTestData::id('uid2');
        $fromUserSet = UserTestData::set([Card::CLUB_4, Card::CLUB_5], true);

        $consecutiveReturns = $this->onConsecutiveCalls(
                                        $toUserSet,
                                        $fromUserSet
                                    );
        $this->redis->method('smembers')->will($consecutiveReturns);

        $this->redis->expects($this->exactly(0))
                    ->method('smove');

        $this->redis->expects($this->once())
                    ->method('hmset')
                    ->with(
                        $id,

                        GameK::PREV_TURN,
                        'u1',

                        GameK::PREV_TURN_TIMESTAMP,
                        $this->isType('int'),

                        GameK::NEXT_TURN,
                        'u2'
                    );

        $this->pubSub->expects($this->once())
                     ->method('trigger')
                     ->with(
                        $id,
                        Event::GAME_MOVE_ACTION,
                        $this->callback(function($payload) {

                            $isSuccess = ($payload['success'] === false);
                            $hasGame   = isset($payload['game']);

                            return $isSuccess && $hasGame;
                        })
                     );

        $now = microtime(true);

        list($success, $game, $toUser) = $this->service
                                              ->moveCard(
                                                    $game,
                                                    $card,
                                                    $fromUserId,
                                                    $toUserId
                                                );

        $this->assertFalse($success);

        $this->assertEquals('u1', $game->prevTurn);
        $this->assertEquals('u2', $game->nextTurn);
        $this->assertLessThan($now, $game->prevTurnTime);

        $this->assertCount(12, $toUser->cards);
        $this->assertNotContains(Card::CLUB_4, $toUser->cards);
    }
}
