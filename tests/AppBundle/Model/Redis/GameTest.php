<?php

namespace Tests\AppBundle\Model\Redis;

use AppBundle\Model\Redis\Game;
use AppBundle\Constant;

/**
 *
 */
class GameTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->gameId   = GameTestData::getId();
        $this->gameHash = GameTestData::getGameHash();

        $this->game = new Game($this->gameId, $this->gameHash);
    }

    public function testConstruct()
    {
        // Assert $game's properties

        $this->assertEquals($this->gameId, $this->game->id);
        $this->assertEquals($this->gameHash["created_at"], $this->game->createdAt);
        $this->assertEquals($this->gameHash["status"], $this->game->status);
        $this->assertEquals($this->gameHash["next_turn"], $this->game->nextTurn);

        $this->assertEquals($this->gameHash["u1"], $this->game->u1);
        $this->assertEquals($this->gameHash["u2"], $this->game->u2);
        $this->assertEquals($this->gameHash["u3"], $this->game->u3);
        $this->assertEquals($this->gameHash["u4"], $this->game->u4);

        $this->assertEquals($this->gameHash["u1_cards"], $this->game->u1Cards);
        $this->assertEquals($this->gameHash["u2_cards"], $this->game->u2Cards);
        $this->assertEquals($this->gameHash["u3_cards"], $this->game->u3Cards);
        $this->assertEquals($this->gameHash["u4_cards"], $this->game->u4Cards);
    }

    public function testIsExpired()
    {
        $this->assertFalse($this->game->isExpired());

        $gameId   = GameTestData::getId();
        $gameHash = GameTestData::getGameHash(["status" => "expired"]);

        $game = new Game($gameId, $gameHash);

        $this->assertTrue($game->isExpired());
    }

    public function testIsUserSNVacant()
    {
        $this->assertFalse($this->game->isUserSNVacant("u1"));

        $this->assertTrue($this->game->isUserSNVacant("u2"));
        $this->assertTrue($this->game->isUserSNVacant("u3"));
        $this->assertTrue($this->game->isUserSNVacant("u4"));
    }

    public function testIsUserSNVacantError()
    {
        $this->expectException(\AppBundle\Exception\BadRequestException::class);

        $this->game->isUserSNVacant("u7");
    }

    public function testIsAnyUserSNVacant()
    {
        $this->assertTrue($this->game->isAnyUserSNVacant());

        $gameId   = GameTestData::getId();
        $gameHash = GameTestData::getGameHash(["u2" => "u_2222222222", "u3" => "u_3333333333", "u4" => "u_4444444444"]);

        $game = new Game($gameId, $gameHash);

        $this->assertFalse($game->isAnyUserSNVacant());
    }

    public function testHasUser()
    {
        $this->assertTrue($this->game->hasUser("u_1111111111"));
        $this->assertFalse($this->game->hasUser("u_0000000000"));
    }

    public function testArePartners()
    {

        $gameId   = GameTestData::getId();
        $gameHash = GameTestData::getGameHash(["u2" => "u_2222222222", "u3" => "u_3333333333", "u4" => "u_4444444444"]);
        $game     = new Game($gameId, $gameHash);

        $this->assertTrue($game->arePartners("u_1111111111", "u_3333333333"));
        $this->assertTrue($game->arePartners("u_2222222222", "u_4444444444"));
        $this->assertFalse($game->arePartners("u_3333333333", "u_2222222222"));
        $this->assertFalse($game->arePartners("u_2222222222", "u_1111111111"));
    }

    public function testGetUserSNById()
    {
        $this->assertEquals("u1", $this->game->getUserSNById("u_1111111111"));
        $this->assertEquals(null, $this->game->getUserSNById("u_0000000000"));

        $gameId   = GameTestData::getId();
        $gameHash = GameTestData::getGameHash(["u2" => "u_2222222222", "u3" => "u_3333333333", "u4" => "u_4444444444"]);
        $game     = new Game($gameId, $gameHash);

        $this->assertEquals("u2", $game->getUserSNById("u_2222222222"));
        $this->assertEquals("u3", $game->getUserSNById("u_3333333333"));
        $this->assertEquals("u4", $game->getUserSNById("u_4444444444"));
    }

    public function testGetNextTurnUserId()
    {
        $this->assertEquals("u_1111111111", $this->game->getNextTurnUserId());
    }

    public function testGetInitialCardsByUserSN()
    {
        $this->assertEquals(
            [Constant\Game\Card::CLUB_1, Constant\Game\Card::CLUB_2],
            $this->game->getInitialCardsByUserSN("u1")
        );
        $this->assertEquals([], $this->game->getInitialCardsByUserSN("u2"));
    }
}
