<?php

namespace Tests\AppBundle\Model\Redis;

use AppBundle\Model\Redis\User;
use AppBundle\Constant\Game\Card;

/**
 *
 */
class UserTest extends \PHPUnit_Framework_TestCase
{

    protected function setUp()
    {
        $this->user  = new User(UserTestData::getId(), UserTestData::getCardSet());
    }

    public function testHasCard()
    {
        $this->assertTrue($this->user->hasCard(Card::CLUB_1));
        $this->assertFalse($this->user->hasCard(Card::DIAMOND_1));
    }

    public function testHasAtLeastOneCardOfType()
    {

        $this->assertTrue($this->user->hasAtLeastOneCardOfType(Card::HEART_2));
        $this->assertTrue($this->user->hasAtLeastOneCardOfType(Card::CLUB_5));

        $this->assertFalse($this->user->hasAtLeastOneCardOfType(Card::HEART_8));
        $this->assertFalse($this->user->hasAtLeastOneCardOfType(Card::DIAMOND_2));
    }

    public function testAddCard()
    {

        $this->assertEquals(5, count($this->user->cards));

        $this->user->addCard(Card::HEART_2);
        $this->user->addCard(Card::HEART_3);

        $this->assertEquals(7, count($this->user->cards));
        $this->assertTrue($this->user->hasCard(Card::HEART_2));
        $this->assertTrue($this->user->hasCard(Card::HEART_3));
    }

    public function testRemoveCard()
    {

        $this->assertEquals(5, count($this->user->cards));

        $this->user->removeCard(Card::HEART_1);

        $this->assertEquals(4, count($this->user->cards));
        $this->assertFalse($this->user->hasCard(Card::HEART_1));

        $this->assertTrue($this->user->hasCard(Card::CLUB_1));
    }
}
