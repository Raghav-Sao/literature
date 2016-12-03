<?php

namespace Tests\AppBundle\Model\Redis;

use AppBundle\Model\Redis\Game;
use AppBundle\Constant;

class GameTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->id   = GameTestData::getId();
        $this->hash = GameTestData::getGameHash();

        $this->game = new Game($this->id, $this->hash);
    }

    public function testConstruct()
    {
        // Assert $game's properties

        $this->assertEquals($this->id, $this->game->id);
        $this->assertEquals($this->hash['created_at'], $this->game->createdAt);
        $this->assertEquals($this->hash['status'], $this->game->status);
        $this->assertEquals($this->hash['next_turn'], $this->game->nextTurn);

        $this->assertEquals($this->hash['u1'], $this->game->u1);
        $this->assertEquals($this->hash['u2'], $this->game->u2);
        $this->assertEquals($this->hash['u3'], $this->game->u3);
        $this->assertEquals($this->hash['u4'], $this->game->u4);

        $this->assertEquals($this->hash['u1_cards'], $this->game->u1Cards);
        $this->assertEquals($this->hash['u2_cards'], $this->game->u2Cards);
        $this->assertEquals($this->hash['u3_cards'], $this->game->u3Cards);
        $this->assertEquals($this->hash['u4_cards'], $this->game->u4Cards);
    }

    public function testIsExpired()
    {
        $this->assertFalse($this->game->isExpired());

        $id   = GameTestData::getId();
        $hash = GameTestData::getGameHash(['status' => 'expired']);

        $game = new Game($id, $hash);

        $this->assertTrue($game->isExpired());
    }

    public function testIsSNVacant()
    {
        $this->assertFalse($this->game->isSNVacant('u1'));

        $this->assertTrue($this->game->isSNVacant('u2'));
        $this->assertTrue($this->game->isSNVacant('u3'));
        $this->assertTrue($this->game->isSNVacant('u4'));
    }

    public function testIsSNVacantError()
    {
        $this->expectException(\AppBundle\Exception\BadRequestException::class);

        $this->game->isSNVacant('u7');
    }

    public function testIsAnySNVacant()
    {
        $this->assertTrue($this->game->isAnySNVacant());

        $id   = GameTestData::getId();
        $hash = GameTestData::getGameHash(
            [
                'u2' => 'u_2222222222',
                'u3' => 'u_3333333333',
                'u4' => 'u_4444444444'
            ]
        );

        $game = new Game($id, $hash);

        $this->assertFalse($game->isAnySNVacant());
    }

    public function testHasUser()
    {
        $this->assertTrue($this->game->hasUser('u_1111111111'));
        $this->assertFalse($this->game->hasUser('u_0000000000'));
    }

    public function testAreTeam()
    {

        $id   = GameTestData::getId();
        $hash = GameTestData::getGameHash(
            [
                'u2' => 'u_2222222222',
                'u3' => 'u_3333333333',
                'u4' => 'u_4444444444'
            ]
        );

        $game = new Game($id, $hash);

        $this->assertTrue($game->areTeam('u_1111111111', 'u_3333333333'));
        $this->assertTrue($game->areTeam('u_2222222222', 'u_4444444444'));
        $this->assertFalse($game->areTeam('u_3333333333', 'u_2222222222'));
        $this->assertFalse($game->areTeam('u_2222222222', 'u_1111111111'));
    }

    public function testGetSNByUserId()
    {
        $this->assertEquals('u1', $this->game->getSNByUserId('u_1111111111'));
        $this->assertEquals(null, $this->game->getSNByUserId('u_0000000000'));

        $id   = GameTestData::getId();
        $hash = GameTestData::getGameHash(
            [
                'u2' => 'u_2222222222',
                'u3' => 'u_3333333333',
                'u4' => 'u_4444444444'
            ]
        );

        $game     = new Game($id, $hash);

        $this->assertEquals('u2', $game->getSNByUserId('u_2222222222'));
        $this->assertEquals('u3', $game->getSNByUserId('u_3333333333'));
        $this->assertEquals('u4', $game->getSNByUserId('u_4444444444'));
    }

    public function testGetNextTurnUserId()
    {
        $this->assertEquals('u_1111111111', $this->game->getNextTurnUserId());
    }

    public function testGetInitCardsBySN()
    {
        $this->assertEquals(
            [Constant\Game\Card::CLUB_1, Constant\Game\Card::CLUB_2],
            $this->game->getInitCardsBySN('u1')
        );
        $this->assertEquals([], $this->game->getInitCardsBySN('u2'));
    }
}
