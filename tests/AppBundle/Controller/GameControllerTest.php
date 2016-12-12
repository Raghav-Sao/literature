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

        $client->request('GET', '/game/start');

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

        $resBody = $this->makeFirstAssertions($res, 200, $expected);

        $game = $resBody->response->game;
        $user = $resBody->response->user;

        $this->assertCount(12, $user->cards);
        $this->assertEquals($game->nextTurn, $user->id);

        //
        // TODO:
        // - Add more asserts
        //
    }

    public function testStartActionWhenSessionGameExists()
    {
    }

    //
    // TODO:
    // - Add more tests
    //   Ensure controller changes are covered.
    //
}
