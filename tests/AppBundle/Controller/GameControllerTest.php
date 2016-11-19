<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Exception;

class GameControllerTest extends AbstractControllerTest
{
    public function testStartAction()
    {
        $client = static::createClient();

        $client->request("GET", "/game/start");
        $res = $client->getResponse();

        $expected = [
            "success"  => true,
            "response" => [
                "game" => [
                    "id"     => "TF_SOMETHING",
                    "status" => "TF_SOMETHING",
                    "u1"     => "TF_SOMETHING",
                    "u2"     => null,
                    "u3"     => null,
                    "u4"     => null
                ],
                "user" => [
                    "id"    => "TF_SOMETHING",
                    "cards" => "TF_SOMETHING"
                ]
            ]
        ];

        $resBody = $this->makeFirstAssertions($res, 200, $expected);
        $this->assertEquals(12, count($resBody->response->user->cards));

        // On reloading the same page, should throw 400 saying you're already in a game
        $client->reload();
        $res = $client->getResponse();

        $expected = [
            "success"   => false,
            "errorCode" => Exception\Code::BAD_REQUEST,
            "errorMessage" => "You are already in an active game",
            "extra"  => [
                "gameId" => $resBody->response->game->id
            ]
        ];

        $resBody = $this->makeFirstAssertions($res, 400, $expected);
    }

    public function testIndexAction()
    {
        $client = static::createClient();

        // Without a game already created, should throw 404
        $client->request("GET", "/game");
        $res = $client->getResponse();

        $expected = [
            "success"      => false,
            "errorCode"    => Exception\Code::NOT_FOUND,
            "errorMessage" => "Game not found",
            "extra"        => []
        ];

        $resBody = $this->makeFirstAssertions($res, 404, $expected);

        // Now after creating a game
        $client->request("GET", "/game/start");
        $res = $client->getResponse();

        $resBody = $this->makeFirstAssertions($res, 200, []);

        $gameId  = $resBody->response->game->id;
        $userId  = $resBody->response->user->id;

        $client->request("GET", "/game");
        $res = $client->getResponse();

        $expected = [
            "success"  => true,
            "response" => [
                "game" => [
                    "id"     => $gameId,
                    "status" => "TF_SOMETHING",
                    "u1"     => $userId,
                    "u2"     => null,
                    "u3"     => null,
                    "u4"     => null
                ],
                "user" => [
                    "id"    => $userId,
                    "cards" => "TF_SOMETHING"
                ]
            ]
        ];

        $resBody = $this->makeFirstAssertions($res, 200, $expected);
        $this->assertEquals(12, count($resBody->response->user->cards));
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
        $client1->request("GET", "/game/start");
        $res = $client1->getResponse();

        $resBody = $this->makeFirstAssertions($res, 200);

        $gameId  = $resBody->response->game->id;
        $userId1  = $resBody->response->user->id;

        // Same user tries to join again
        $client1->request("POST", sprintf("/game/%s/join/u2", $gameId));
        $res = $client1->getResponse();

        $expected = [
            "success"      => false,
            "errorCode"    => Exception\Code::BAD_REQUEST,
            "errorMessage" => "You are already in an active game",
            "extra"        => [
                "gameId" => $gameId
            ]
        ];

        $resBody = $this->makeFirstAssertions($res, 400, $expected);

        // Other user joins with invalid gameId
        $client2 = static::createClient();
        $client2->request("POST", sprintf("/game/%s_invalid/join/u2", $gameId));
        $res = $client2->getResponse();

        $expected = [
            "success"      => false,
            "errorCode"    => Exception\Code::NOT_FOUND,
            "errorMessage" => "Game not found",
            "extra"        => []
        ];

        $resBody = $this->makeFirstAssertions($res, 404, $expected);

        // Other user joins with invalid serial number
        $client2->request("POST", sprintf("/game/%s/join/u8", $gameId));
        $res = $client2->getResponse();

        $expected = [
            "success"      => false,
            "errorCode"    => Exception\Code::BAD_REQUEST,
            "errorMessage" => "Invalid user serial number",
            "extra"        => []
        ];

        $resBody = $this->makeFirstAssertions($res, 400, $expected);

        // Other user joins at occupied serial number
        $client2->request("POST", sprintf("/game/%s/join/u1", $gameId));
        $res = $client2->getResponse();

        $expected = [
            "success"      => false,
            "errorCode"    => Exception\Code::BAD_REQUEST,
            "errorMessage" => "Invalid position to join as member",
            "extra"        => []
        ];

        $resBody = $this->makeFirstAssertions($res, 400, $expected);

        // Other user joins
        $client2->request("POST", sprintf("/game/%s/join/u3", $gameId));
        $res = $client2->getResponse();

        $expected = [
            "success"  => true,
            "response" => [
                "game" => [
                    "id"     => $gameId,
                    "status" => "TF_SOMETHING",
                    "u1"     => $userId1,
                    "u2"     => null,
                    "u3"     => "TF_SOMETHING",
                    "u4"     => null
                ],
                "user" => [
                    "id"    => "TF_SOMETHING",
                    "cards" => "TF_SOMETHING"
                ]
            ]
        ];

        $resBody = $this->makeFirstAssertions($res, 200, $expected);
        $this->assertEquals(12, count($resBody->response->user->cards));
    }

    public function testDeleteAction()
    {
        $client = static::createClient();

        // Without a game already created, should throw 404
        $client->request("DELETE", "/game/delete");
        $res = $client->getResponse();

        $expected = [
            "success"      => false,
            "errorCode"    => Exception\Code::NOT_FOUND,
            "errorMessage" => "Game not found",
            "extra"        => []
        ];

        $resBody = $this->makeFirstAssertions($res, 404, $expected);

        // Now after starting a game
        $client->request("GET", "/game/start");
        $client->request("DELETE", "/game/delete");
        $res = $client->getResponse();

        $expected = [
            "success" => true,
            "response" => [
                "success" => true
            ]
        ];

        $resBody = $this->makeFirstAssertions($res, 200, $expected);
    }
}
