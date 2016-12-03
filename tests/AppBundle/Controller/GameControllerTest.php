<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Exception;
use AppBundle\Constant;
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
                    'status' => Constant\Game\Status::INITIALIZED,
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
            'errorMessage' => 'You are already in an active game',
            'extra'        => [
                'gameId'   => $resBody->response->game->id,
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
                    'status' => Constant\Game\Status::INITIALIZED,
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
        // Covers:
        // - Check if user already in game
        // - Valid gameId
        // - Valid atSn
        // - Is atSn vacant

        // Creates a game
        $client1 = static::createClient();
        $client1->request('GET', '/game/start');
        $res = $client1->getResponse();

        $resBody = $this->makeFirstAssertions($res, 200);

        $gameId   = $resBody->response->game->id;
        $userId1  = $resBody->response->user->id;

        // Same user tries to join again
        $client1->request('POST', sprintf('/game/%s/join/u2', $gameId));
        $res = $client1->getResponse();

        $expected = [
            'success'      => false,
            'errorCode'    => Exception\Code::BAD_REQUEST,
            'errorMessage' => 'You are already in an active game',
            'extra'        => [
                'gameId' => $gameId,
            ],
        ];

        $resBody = $this->makeFirstAssertions($res, 400, $expected);

        // Other user joins with invalid gameId
        $client2 = static::createClient();
        $client2->request('POST', sprintf('/game/%s_invalid/join/u2', $gameId));
        $res = $client2->getResponse();

        $expected = [
            'success'      => false,
            'errorCode'    => Exception\Code::NOT_FOUND,
            'errorMessage' => 'Game not found',
            'extra'        => [],
        ];

        $resBody = $this->makeFirstAssertions($res, 404, $expected);

        // Other user joins with invalid serial number
        $client2->request('POST', sprintf('/game/%s/join/u8', $gameId));
        $res = $client2->getResponse();

        $expected = [
            'success'      => false,
            'errorCode'    => Exception\Code::BAD_REQUEST,
            'errorMessage' => 'Invalid user serial number',
            'extra'        => [],
        ];

        $resBody = $this->makeFirstAssertions($res, 400, $expected);

        // Other user joins at occupied serial number
        $client2->request('POST', sprintf('/game/%s/join/u1', $gameId));
        $res = $client2->getResponse();

        $expected = [
            'success'      => false,
            'errorCode'    => Exception\Code::BAD_REQUEST,
            'errorMessage' => 'Invalid position to join as member',
            'extra'        => [],
        ];

        $resBody = $this->makeFirstAssertions($res, 400, $expected);

        // Other user joins
        $client2->request('POST', sprintf('/game/%s/join/u2', $gameId));
        $res = $client2->getResponse();

        $expected = [
            'success'  => true,
            'response' => [
                'game' => [
                    'id'     => $gameId,
                    'status' => Constant\Game\Status::INITIALIZED,
                    'u1'     => $userId1,
                    'u2'     => TestFlag::SOMETHING,
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

        $client3 = static::createClient();
        $client3->request('POST', sprintf('/game/%s/join/u3', $gameId));
        $res = $client3->getResponse();

        $client4 = static::createClient();
        $client4->request('POST', sprintf('/game/%s/join/u4', $gameId));
        $res = $client4->getResponse();

        $expected = [
            'success'  => true,
            'response' => [
                'game' => [
                    'id'     => $gameId,
                    'status' => Constant\Game\Status::ACTIVE,
                    'u1'     => $userId1,
                    'u2'     => TestFlag::SOMETHING,
                    'u3'     => TestFlag::SOMETHING,
                    'u4'     => TestFlag::SOMETHING,
                ],
                'user' => [
                    'id'    => TestFlag::SOMETHING,
                    'cards' => TestFlag::SOMETHING,
                ],
            ],
        ];

        $resBody = $this->makeFirstAssertions($res, 200, $expected);
    }

    public function testMoveAction()
    {
        // Covers:
        //
        // - Is valid card
        // - The other use doesn't exist in game
        // - These two are partners
        // - It is not his turn

        // Following will have to cover in unit test
        // Functional tests for these use cases are flaky :)
        //
        // - You do not have that card
        // - 2 cases:
        //   - The other user has the card
        //   - The other user doesn't have the card

        // Client1 starts a game
        $client1 = static::createClient();
        $client1->request('GET', '/game/start');

        $res = $client1->getResponse();

        $resBody1 = $this->makeFirstAssertions($res, 200);

        $gameId       = $resBody1->response->game->id;
        $userId1      = $resBody1->response->user->id;
        $userId1Cards = $resBody1->response->user->cards;

        // Other clients joins
        $client2 = static::createClient();
        $client2->request('POST', sprintf('/game/%s/join/u2', $gameId));
        $res = $client2->getResponse();

        $resBody2 = $this->makeFirstAssertions($res, 200);

        $userId2      = $resBody2->response->user->id;
        $userId2Cards = $resBody2->response->user->cards;

        $client3 = static::createClient();
        $client3->request('POST', sprintf('/game/%s/join/u3', $gameId));
        $res = $client3->getResponse();

        $resBody3 = $this->makeFirstAssertions($res, 200);

        $userId3      = $resBody3->response->user->id;
        $userId3Cards = $resBody3->response->user->cards;

        $client4 = static::createClient();
        $client4->request('POST', sprintf('/game/%s/join/u4', $gameId));
        $res = $client4->getResponse();

        $resBody4 = $this->makeFirstAssertions($res, 200);

        $userId4      = $resBody4->response->user->id;
        $userId4Cards = $resBody4->response->user->cards;

        // Attempt invalid card move
        $url = sprintf('/game/move/%s/from/%s', Constant\Game\Card::CLUB_7, $userId2);
        $client1->request('POST', $url);
        $res = $client1->getResponse();

        $expected = [
            'success'      => false,
            'errorCode'    => Exception\Code::BAD_REQUEST,
            'errorMessage' => 'Not a valid card',
            'extra'        => [],
        ];

        $resBody = $this->makeFirstAssertions($res, 400, $expected);

        // Attempt with invalid {fromUserId}
        $url = sprintf('/game/move/%s/from/%s', Constant\Game\Card::CLUB_6, 'invalid_id');
        $client1->request('POST', $url);
        $res = $client1->getResponse();

        $expected = [
            'success'      => false,
            'errorCode'    => Exception\Code::BAD_REQUEST,
            'errorMessage' => 'Bad value for fromUserId, Does not exists',
            'extra'        => [],
        ];

        $resBody = $this->makeFirstAssertions($res, 400, $expected);

        // Attempt with {fromUserId} = The other partner
        $url = sprintf('/game/move/%s/from/%s', Utility::getRandomCard($userId3Cards), $userId3);
        $client1->request('POST', $url);
        $res = $client1->getResponse();

        $expected = [
            'success'      => false,
            'errorCode'    => Exception\Code::BAD_REQUEST,
            'errorMessage' => 'Bad value for fromUserId, You are partners',
            'extra'        => [],
        ];

        $resBody = $this->makeFirstAssertions($res, 400, $expected);

        // Attempt when it is not your turn
        $url = sprintf('/game/move/%s/from/%s', Utility::getRandomCard($userId3Cards), $userId3);
        $client2->request('POST', $url);
        $res = $client2->getResponse();

        $expected = [
            'success'      => false,
            'errorCode'    => Exception\Code::BAD_REQUEST,
            'errorMessage' => 'It is not your turn to make a move',
            'extra'        => [],
        ];

        $resBody = $this->makeFirstAssertions($res, 400, $expected);
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
}
