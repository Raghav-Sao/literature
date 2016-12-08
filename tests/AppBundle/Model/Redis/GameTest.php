<?php

namespace Tests\AppBundle\Model\Redis;

use AppBundle\Model\Redis\Game;
use AppBundle\Constant\Game\Game as GameK;

class GameTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $id   = GameTestData::id();
        $hash = GameTestData::hash();

        $game = new Game($id, $hash);

        // Assert $game's properties

        $this->assertEquals($id, $game->id);
        $this->assertEquals($hash['created_at'], $game->createdAt);
        $this->assertEquals($hash['status'], $game->status);
        $this->assertEquals($hash['next_turn'], $game->nextTurn);

        $this->assertEquals($hash['u1'], $game->u1);
        $this->assertEquals($hash['u2'], $game->u2);
        $this->assertEquals($hash['u3'], $game->u3);
        $this->assertEquals($hash['u4'], $game->u4);

        $this->assertEquals($hash['u1_cards'], $game->u1Cards);
        $this->assertEquals($hash['u2_cards'], $game->u2Cards);
        $this->assertEquals($hash['u3_cards'], $game->u3Cards);
        $this->assertEquals($hash['u4_cards'], $game->u4Cards);

        $teams = ['t1' => ['uid1', 'uid3'], 't2' => ['uid2', null]];
        $this->assertEquals($teams, $game->teams);

        $index = ['uid1' => 'u1', 'uid2' => 'u2', 'uid3' => 'u3', null => 'u4'];
        $this->assertEquals($index, $game->index);
    }

    public function testGetters()
    {
        $id = GameTestData::id();
        $hash = GameTestData::hash();

        $game = new Game($id, $hash);

        $this->assertFalse($game->isActive());

        $this->assertFalse($game->isExpired());

        $this->assertTrue($game->isNotExpired());

        $this->assertFalse($game->isSNVacant(GameK::U1));
        $this->assertTrue($game->isSNVacant(GameK::U4));

        $this->assertTrue($game->isAnySNVacant());

        $this->assertTrue($game->hasUser('uid1'));
        $this->assertFalse($game->hasUser('uid4'));

        $this->assertFalse($game->areTeam('uid1', 'uid2'));
        $this->assertTrue($game->areTeam('uid1', 'uid3'));

        $this->assertEquals('u1', $game->getSNByUserId($game->u1));

        $this->assertEquals('uid1', $game->getNextTurnUserId());

        $this->assertCount(12, $game->getInitCardsBySN('u1'));
        $this->assertCount(0, $game->getInitCardsBySN('u4'));
        // Invalid id
        $this->assertCount(0, $game->getInitCardsBySN('u5'));

        $teamUsers = $game->getTeamUsers(GameK::TEAM_1);
        $this->assertCount(2, $teamUsers);
        $this->assertContains('uid1', $teamUsers);
        $this->assertContains('uid3', $teamUsers);

        $this->assertEquals(GameK::TEAM_1, $game->getTeam('uid1'));
        $this->assertEquals(GameK::TEAM_2, $game->getTeam('uid2'));

        $this->assertEquals(GameK::TEAM_2, $game->getOppTeam('uid3'));
        $this->assertEquals(GameK::TEAM_1, $game->getOppTeam('uid4'));

        $game->incrPoint('uid1', 0.5);
        $this->assertEquals(0.5, $game->u1Points);
        $game->incrPoint('uid1', 0.8);
        $this->assertEquals(1.3, $game->u1Points);

        $this->assertEquals(0, $game->u2Points);
    }


    /**
     * @expectedException        \AppBundle\Exception\BadRequestException
     * @expectedExceptionMessage Invalid user serial number
     */
    public function testIsSNVacantWithBadInput()
    {
        $id   = GameTestData::id();
        $hash = GameTestData::hash();

        $game = new Game($id, $hash);

        $game->isSNVacant('u5');
    }
}
