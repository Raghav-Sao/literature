<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Exception;
use AppBundle\Constant\Game\Status;
use AppBundle\Constant\Game\Card;
use AppBundle\Utility;

class GameControllerTest extends AbstractControllerTest
{
    public function testStartAction()
    {
        $client = static::createClient();

        $client->request('POST', '/game/start');

        $res = $client->getResponse();

        $expected = [
            'success'  => true,
            'response' => [
                'game' => [
                    'id'           => TestFlag::SOMETHING,
                    'createdAt'    => TestFlag::SOMETHING,
                    'status'       => Status::INITIALIZED,
                    'prevTurn'     => null,
                    'prevTurnTime' => TestFlag::SOMETHING,
                    'nextTurn'     => TestFlag::SOMETHING,
                    'usersCount'   => 1,
                    'users'        => TestFlag::SOMETHING,
                    'team0'        => TestFlag::SOMETHING,
                    'team1'        => TestFlag::SOMETHING,
                    'points'       => TestFlag::SOMETHING,
                ],
                'user' => [
                    'id'    => TestFlag::SOMETHING,
                    'cards' => TestFlag::SOMETHING,
                ],
            ],
        ];

        $content = $this->makeFirstAssertions($res, 200, $expected);

        $game = & $content['response']['game'];
        $user = & $content['response']['user'];

        $this->assertEquals($user['id'], $game['nextTurn']);
        $this->assertEquals(1, $game['usersCount']);
        $this->assertCount(1, $game['users']);
        $this->assertContains($user['id'], $game['users']);
        $this->assertCount(1, $game['team0']);
        $this->assertContains($user['id'], $game['team0']);
        $this->assertCount(0, $game['team1']);
        $this->assertCount(1, $game['points']);
        $this->assertCount(1, $game['points']);
        $this->assertEquals(0, $game['points'][$user['id']]);

        $this->assertCount(12, $user['cards']);
    }

    public function testStartActionWhenSessionGameExists()
    {
        $client = static::createClient();

        $client->request('POST', '/game/start');

        $res = $client->getResponse();

        //
        // Another request for same client should fail
        //

        $content = $this->makeFirstAssertions($res, 200);

        $gameId = $content['response']['game']['id'];

        $client->request('POST', '/game/start');

        $res = $client->getResponse();

        $expected = [
            'success'      => false,
            'errorCode'    => 'BAD_REQUEST',
            'errorMessage' => 'A game exists in session already.',
            'extra'        => [
                'gameId' => $gameId,
            ],
        ];

        $this->makeFirstAssertions($res, 400, $expected);
    }
}
