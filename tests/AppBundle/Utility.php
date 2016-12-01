<?php

namespace Tests\AppBundle;

use AppBundle\Utility;
use AppBundle\Constant\Game;

/**
 *
 */
class UtilityTest extends \PHPUnit_Framework_TestCase
{

    public function testCamelize()
    {
        $this->assertEquals("userId", Utility::camelizeLcFirst("user_id"));
    }

    public function testIsValidCard()
    {
        $this->assertEquals(false, Utility::isValidCard(Game\Card::HEART_7));
        $this->assertEquals(true, Utility::isValidCard(Game\Card::HEART_11));
    }
}
