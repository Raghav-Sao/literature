<?php

namespace Tests\AppBundle\Model\Redis;

use AppBundle\Constant\Game\Card;

class UserTestData
{

    public static $id              = 'u_1111111111';

    public static $defaultCardSet  = [
        Card::CLUB_1,
        Card::CLUB_2,
        Card::CLUB_3,
        Card::CLUB_4,

        Card::HEART_1,
    ];

    public static function getId(
        string $id = null
    )
    {
        if (empty($id))
        {
            return self::$id;
        }
        else
        {
            return $id;
        }
    }

    public static function getCardSet(
        array $overrideWith = [],
        bool  $replace      = false
    )
    {
        if ($replace)
        {
            return $overrideWith;
        }
        else
        {
            return array_merge(self::$defaultCardSet, $overrideWith);
        }
    }
}
