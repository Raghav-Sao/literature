<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class GameControllerTest extends WebTestCase
{
    public function testStartAction()
    {
        $client = static::createClient();

        $client->request('GET', '/game/start');
        $res = $client->getResponse();

        $this->assertEquals(200, $res->getStatusCode());
        $this->assertTrue($res->headers->contains('Content-Type', 'application/json'));

        $resBody = json_decode($res->getContent());
        $this->assertTrue(property_exists($resBody, "success"));
        $this->assertTrue($resBody->success);
        $this->assertTrue(property_exists($resBody, "response"));
        $this->assertTrue(property_exists($resBody->response, "game"));
        $this->assertTrue(property_exists($resBody->response, "user"));

        $this->assertTrue(property_exists($resBody->response->game, "id"));
        $this->assertTrue(property_exists($resBody->response->game, "status"));
        $this->assertTrue(property_exists($resBody->response->game, "u1"));
        $this->assertTrue(property_exists($resBody->response->game, "u2"));
        $this->assertTrue(property_exists($resBody->response->game, "u3"));
        $this->assertTrue(property_exists($resBody->response->game, "u4"));

        $this->assertTrue(property_exists($resBody->response->user, "id"));
        $this->assertTrue(property_exists($resBody->response->user, "cards"));
        $this->assertEquals(12, count($resBody->response->user->cards));
    }
}
