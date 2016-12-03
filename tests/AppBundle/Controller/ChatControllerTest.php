<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Exception;

class ChatControllerTest extends AbstractControllerTest
{
    public function testPostAction()
    {
        // TODO:
        // - Fix this: Content-Type is not going as application/json

        $this->markTestSkipped();

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

        // Now start a game first and then post a chat message
        $client->request('GET', '/game/start');

        //     With a message
        $client->request(
            'POST',
            '/chat',
            [],
            [],
            [
                'Content-Type' => 'application/json',
            ],
            "{'message': 'A sample message..'}"
        );
        $res = $client->getResponse();

        $expected = [
            'success'  => true,
            'response' => null,
        ];

        $resBody = $this->makeFirstAssertions($res, 200, $expected);

        //     Without message or empty message
        $client->request('POST', '/chat');
        $res = $client->getResponse();

        $expected = [
            'success'      => false,
            'errorCode'    => Exception\Code::BAD_REQUEST,
            'errorMessage' => 'No message provided in input',
            'extra'        => [],
        ];

        $resBody = $this->makeFirstAssertions($res, 400, $expected);

        $client->request(
            'POST',
            '/chat',
            [],
            [],
            [
                'Content-Type' => 'application/json',
            ],
            "{'message': ''}"
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
