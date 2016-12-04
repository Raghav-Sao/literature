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
                    'id'     => TestFlag::SOMETHING,
                    'status' => Status::INITIALIZED,
                    'u1'     => TestFlag::SOMETHING,
                    'u2'     => null,
                    'u3'     => null,
                    'u4'     => null,
                ],
                'user' => [
                    'id'    => TestFlag::SOMETHING,
                    'cards' => TestFlag::SOMETHING,
                ],
            ],
        ];

        $resBody = $this->makeFirstAssertions($res, 200, $expected);

        $this->assertCount(12, $resBody->response->user->cards);

        // On reloading the same page,
        // should throw 400 saying you're already in a game

        $client->reload();
        $res = $client->getResponse();

        $expected = [
            'success'      => false,
            'errorCode'    => Exception\Code::BAD_REQUEST,
            'errorMessage' => 'You appear to be active in a game',
            'extra'        => [
                'id'       => $resBody->response->game->id,
            ],
        ];

        $resBody = $this->makeFirstAssertions($res, 400, $expected);
    }

    public function testIndexAction()
    {
        $client = static::createClient();

        // Without a game already created, should throw 404
        $client->request('GET', '/game');

        $res = $client->getResponse();

        $expected = [
            'success'      => false,
            'errorCode'    => Exception\Code::NOT_FOUND,
            'errorMessage' => 'Game not found',
            'extra'        => [],
        ];

        $resBody = $this->makeFirstAssertions($res, 404, $expected);

        // Now after creating a game

        $client->request('GET', '/game/start');

        $res = $client->getResponse();

        $resBody = $this->makeFirstAssertions($res, 200, []);

        $gameId  = $resBody->response->game->id;
        $userId  = $resBody->response->user->id;

        $client->request('GET', '/game');

        $res = $client->getResponse();

        $expected = [
            'success'  => true,
            'response' => [
                'game' => [
                    'id'     => $gameId,
                    'status' => Status::INITIALIZED,
                    'u1'     => $userId,
                    'u2'     => null,
                    'u3'     => null,
                    'u4'     => null,
                ],
                'user' => [
                    'id'    => $userId,
                    'cards' => TestFlag::SOMETHING,
                ],
            ],
        ];

        $resBody = $this->makeFirstAssertions($res, 200, $expected);

        $this->assertCount(12, $resBody->response->user->cards);
    }

    public function testJoinAction()
    {
    }

    public function testMoveAction()
    {
    }

    public function testDeleteAction()
    {
        $client = static::createClient();

        // Without a game already created, should throw 404

        $client->request('DELETE', '/game/delete');

        $res = $client->getResponse();

        $expected = [
            'success'      => false,
            'errorCode'    => Exception\Code::NOT_FOUND,
            'errorMessage' => 'Game not found',
            'extra'        => [],
        ];

        $resBody = $this->makeFirstAssertions($res, 404, $expected);

        // Now after starting a game

        $client->request('GET', '/game/start');

        $client->request('DELETE', '/game/delete');

        $res = $client->getResponse();

        $expected = [
            'success' => true,
            'response' => [
                'success' => true,
            ],
        ];

        $resBody = $this->makeFirstAssertions($res, 200, $expected);
    }

    // ----------------------------------------------------------------------
    // Protected methods


}
