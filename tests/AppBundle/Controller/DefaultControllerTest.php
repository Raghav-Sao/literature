<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Exception;

class DefaultControllerTest extends AbstractControllerTest
{
    public function testIndexWithoutGame()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertContains('Start a game', $crawler->filter('a')->text());
    }

    public function testIndexWithGame()
    {
        $client = static::createClient();

        // Start a game

        $client->request('GET', '/game/start');

        $res = $client->getResponse();

        $resBody = $this->makeFirstAssertions($res, 200);

        // Now, visit index page

        $client->request('GET', '/');

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
}
