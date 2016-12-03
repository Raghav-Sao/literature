<?php

namespace Tests\AppBundle\Service;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

use AppBundle\Service;
use AppBundle\Exception;
use AppBundle\Model\Redis;
use AppBundle\Constant\Game;
use Tests\AppBundle\Model\Redis\GameTestData;
use Tests\AppBundle\Model\Redis\UserTestData;

class GameTest extends KernelTestCase
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

    /**
     * @expectedException        \AppBundle\Exception\NotFoundException
     * @expectedExceptionMessage Game not found
     *
     */
    public function testGetWhenNotFound()
    {
        $this->redis->method('hgetall')
                    ->willReturn(null);

        $this->gameService->get(GameTestData::getId());
    }

    /**
     * @expectedException        \AppBundle\Exception\NotFoundException
     * @expectedExceptionMessage Cards not found for given user
     *
     */
    public function testGetUserWhenNotFound()
    {
        $this->redis->method('smembers')
                    ->willReturn(null);

        $this->gameService->getUser(UserTestData::getId());
    }

    /**
     * @expectedException        \AppBundle\Exception\BadRequestException
     * @expectedExceptionMessage Game with given id is no longer active
     *
     */
    public function testGetAndValidateWhenGameIsExpired()
    {
        $this->redis->method('hgetall')
                    ->willReturn(
                        GameTestData::getGameHash(['status' => 'expired'])
                    );

        $this->gameService
             ->getAndValidate(
                 GameTestData::getId(),
                 UserTestData::getId()
             );
    }

    /**
     * @expectedException        \AppBundle\Exception\BadRequestException
     * @expectedExceptionMessage You do not belong to game with given id
     *
     */
    public function testGetAndValidateWhenUserIsNotValid()
    {
        $this->redis->method('hgetall')
                    ->willReturn(GameTestData::getGameHash());

        $this->gameService
             ->getAndValidate(
                 GameTestData::getId(),
                 UserTestData::getId('u_2222222222')
             );
    }



    // Tests: Move card

    /**
     * @expectedException        \AppBundle\Exception\BadRequestException
     * @expectedExceptionMessage Game is not active
     *
     */
    public function testMoveCardWhenGameIsNotActive()
    {
        $game = new Redis\Game(GameTestData::getId(), GameTestData::getGameHash());

        $this->gameService->moveCard($game, Game\Card::CLUB_5, 'u_2222222222', 'u_1111111111');
    }

    /**
     * @expectedException        \AppBundle\Exception\BadRequestException
     * @expectedExceptionMessage Bad value for card, You have it already
     *
     */
    public function testMoveCardWhenYouOnlyHaveTheCard()
    {
        $game = new Redis\Game(
            GameTestData::getId(),
            GameTestData::getGameHash(['status' => 'active', 'u2' => 'u_2222222222'])
        );

        $this->redis->method('smembers')
                    ->willReturn(UserTestData::getCardSet());

        $this->gameService->moveCard($game, Game\Card::CLUB_1, 'u_2222222222', 'u_1111111111');
    }

    /**
     * @expectedException        \AppBundle\Exception\BadRequestException
     * @expectedExceptionMessage You do not have at least one card of that type
     *
     */
    public function testMoveCardWhenInvalidCardTypeAndRange()
    {
        $game = new Redis\Game(
            GameTestData::getId(),
            GameTestData::getGameHash(['status' => 'active', 'u2' => 'u_2222222222'])
        );

        $this->redis->method('smembers')
                    ->willReturn(UserTestData::getCardSet());

        $this->gameService->moveCard($game, Game\Card::CLUB_8, 'u_2222222222', 'u_1111111111');
    }

    public function testMoveCardWhenFromUserHaveCard()
    {
        $game = new Redis\Game(
            GameTestData::getId(),
            GameTestData::getGameHash([
                'status' => 'active',

                'u2' => 'u_2222222222',
                'u3' => 'u_3333333333',
                'u4' => 'u_4444444444',
            ])
        );

        $this->redis->method('smembers')
                    ->will(
                        $this->onConsecutiveCalls(
                            UserTestData::getCardSet(),
                            UserTestData::getCardSet(
                                [
                                    Game\Card::CLUB_5,
                                ],
                                true
                            )
                        )
                    );

        $this->redis->method('smove')
                    ->willReturn(null);

        list($success, $game, $user) = $this->gameService
                                            ->moveCard(
                                                $game,
                                                Game\Card::CLUB_5,
                                                'u_2222222222',
                                                'u_1111111111'
                                            );

        $this->assertTrue($success);
        $this->assertEquals('u1', $game->nextTurn);
        $this->assertContains(Game\Card::CLUB_5, $user->cards);
        $this->assertCount(6, $user->cards);
    }

    public function testMoveCardWhenFromUserNotHaveCard()
    {
        $game = new Redis\Game(
            GameTestData::getId(),
            GameTestData::getGameHash([
                'status' => 'active',

                'u2' => 'u_2222222222',
                'u3' => 'u_3333333333',
                'u4' => 'u_4444444444',
            ])
        );

        $this->redis->method('smembers')
                    ->will(
                        $this->onConsecutiveCalls(
                            UserTestData::getCardSet(),
                            UserTestData::getCardSet(
                                [
                                    Game\Card::CLUB_5,
                                ],
                                true
                            )
                        )
                    );

        $this->redis->method('hmset')
                    ->willReturn(null);

        list($success, $game, $user) = $this->gameService
                                            ->moveCard(
                                                $game,
                                                Game\Card::HEART_2,
                                                'u_2222222222',
                                                'u_1111111111'
                                            );

        $this->assertFalse($success);
        $this->assertEquals('u2', $game->nextTurn);
        $this->assertNotContains(Game\Card::HEART_2, $user->cards);
        $this->assertCount(5, $user->cards);
    }
}
