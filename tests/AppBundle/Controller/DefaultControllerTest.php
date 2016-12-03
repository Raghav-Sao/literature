<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Exception;

class DefaultControllerTest extends AbstractControllerTest
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertContains('Start a game', $crawler->filter('a')->text());

        // Now after creating a game, index page should throw 400
        $client->request('GET', '/game/start');
        $res = $client->getResponse();

        $resBody = $this->makeFirstAssertions($res, 200);

        $client->request('GET', '/');
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
}
