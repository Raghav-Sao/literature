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
        $this->id    = "u_1111111111";
        $this->cards = [
            Card::CLUB_1,
            Card::CLUB_2,
            Card::CLUB_3,
            Card::CLUB_4,

            Card::HEART_1,
        ];

        $this->user  = new User($this->id, $this->cards);
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
}
