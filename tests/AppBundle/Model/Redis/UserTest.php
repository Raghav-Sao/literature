<?php

namespace Tests\AppBundle\Model\Redis;

use AppBundle\Model\Redis\User;
use AppBundle\Constant\Game\Card;

class UserTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $id   = UserTestData::id();
        $set  = UserTestData::set();

        $user = new User($id, $set);

        // Assert $game's properties

        $this->assertEquals($id, $user->id);

        // TODO:
        // - Assert other fields too..
    }

    public function testHasCard()
    {
        $id   = UserTestData::id();
        $set  = UserTestData::set();

        $user = new User($id, $set);

        $this->assertTrue($user->hasCard(Card::CLUB_1));
        $this->assertFalse($user->hasCard(Card::CLUB_5));
    }

    public function testHasAtLeastOneCardOfType()
    {
        $id   = UserTestData::id();
        $set  = UserTestData::set();

        $user = new User($id, $set);

        $this->assertTrue($user->hasAtLeastOneCardOfType(Card::CLUB_1));
        $this->assertTrue($user->hasAtLeastOneCardOfType(Card::SPADE_5));

        $this->assertFalse($user->hasAtLeastOneCardOfType(Card::CLUB_8));
        $this->assertFalse($user->hasAtLeastOneCardOfType(Card::SPADE_8));
    }

    public function testAddCard()
    {
        $id   = UserTestData::id();
        $set  = UserTestData::set();

        $user = new User($id, $set);

        $this->assertCount(12, $user->cards);

        $user->addCard(Card::CLUB_8);
        $user->addCard(Card::SPADE_8);

        $this->assertCount(14, $user->cards);
        $this->assertTrue($user->hasCard(Card::CLUB_8));
        $this->assertTrue($user->hasCard(Card::SPADE_8));
    }

    public function testRemoveCard()
    {
        $id   = UserTestData::id();
        $set  = UserTestData::set();

        $user = new User($id, $set);

        $this->assertCount(12, $user->cards);

        $user->removeCard(Card::CLUB_1);

        $this->assertCount(11, $user->cards);
        $this->assertFalse($user->hasCard(Card::CLUB_1));
    }

    public function testRemoveCards()
    {
        $id   = UserTestData::id();
        $set  = UserTestData::set();

        $user = new User($id, $set);

        $this->assertCount(12, $user->cards);

        $user->removeCards([Card::CLUB_1, Card::CLUB_2]);

        $this->assertCount(10, $user->cards);
        $this->assertFalse($user->hasCard(Card::CLUB_1));
        $this->assertFalse($user->hasCard(Card::CLUB_2));
    }
}
