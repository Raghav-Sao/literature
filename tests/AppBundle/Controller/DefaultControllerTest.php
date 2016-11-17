<?php

namespace Tests\AppBundle\Controller;

class DefaultControllerTest extends AbstractControllerTest
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request("GET", "/");

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertContains("Start a game", $crawler->filter("a")->text());
    }
}
