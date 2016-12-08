<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Exception;

class ChatControllerTest extends AbstractControllerTest
{
    public function testPostActionWithoutGame()
    {
        $client = static::createClient();

        $client->request('POST', '/chat');

        $res = $client->getResponse();

        $expected = [
            'success'      => false,
            'errorCode'    => Exception\Code::NOT_FOUND,
            'errorMessage' => 'Game not found',
            'extra'        => [],
        ];

        $resBody = $this->makeFirstAssertions($res, 404, $expected);
    }

    public function testPostActionWithMessage()
    {
        $client = static::createClient();

        $client->request('GET', '/game/start');

        $client->request(
            'POST',
            '/chat',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            '{"message": "A sample message.."}'
        );

        $res = $client->getResponse();

        $expected = [
            'success'  => true,
            'response' => null,
        ];

        $resBody = $this->makeFirstAssertions($res, 200, $expected);
    }

    public function testPostActionWithoutMessage()
    {
        $client = static::createClient();

        $client->request('GET', '/game/start');

        $client->request('POST', '/chat');

        $res = $client->getResponse();

        $expected = [
            'success'      => false,
            'errorCode'    => Exception\Code::BAD_REQUEST,
            'errorMessage' => 'No message provided in input',
            'extra'        => [],
        ];

        $resBody = $this->makeFirstAssertions($res, 400, $expected);
    }

    public function testPostActionWithEmptyMessage()
    {
        $client = static::createClient();

        $client->request('GET', '/game/start');

        $client->request(
            'POST',
            '/chat',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            '{"message": ""}'
        );

        $res = $client->getResponse();

        $expected = [
            'success'      => false,
            'errorCode'    => Exception\Code::BAD_REQUEST,
            'errorMessage' => 'No message provided in input',
            'extra'        => [],
        ];

        $resBody = $this->makeFirstAssertions($res, 400, $expected);
    }
}
