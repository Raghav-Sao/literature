<?php

namespace Tests\AppBundle;

use AppBundle\Utility;
use AppBundle\Constant\Game\Card;

class UtilityTest extends \PHPUnit_Framework_TestCase
{

    public function testCamelize()
    {
        $this->assertEquals("userId", Utility::camelizeLcFirst("user_id"));
    }

    public function testIsValidCard()
    {
        $this->assertEquals(false, Utility::isValidCard(Card::HEART_7));
        $this->assertEquals(true, Utility::isValidCard(Card::HEART_11));
    }

    public function testFilterCardsByTypeAndRange()
    {
        $cards = [
            Card::HEART_7,
            Card::HEART_11,
            Card::HEART_1,
            Card::HEART_2,
            Card::HEART_5,
        ];

        $filteredCards = Utility::filterCardsByTypeAndRange($cards, Card::HEART_TYPE, Card::LOWER_RANGE);

        $this->assertCount(3, $filteredCards);

        $this->assertContains(Card::HEART_1, $filteredCards);
        $this->assertContains(Card::HEART_2, $filteredCards);
        $this->assertContains(Card::HEART_5, $filteredCards);

        $this->assertNotContains(Card::HEART_7, $filteredCards);
        $this->assertNotContains(Card::HEART_11, $filteredCards);
    }
}
