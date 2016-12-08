<?php

namespace Tests\AppBundle\Model\Redis;

use AppBundle\Constant\Game\Card;

class UserTestData
{

    public static $id  = 'uid1';

    public static $set = [
        Card::CLUB_1,
        Card::CLUB_2,
        Card::CLUB_3,
        Card::SPADE_1,
        Card::SPADE_2,
        Card::SPADE_3,
        Card::DIAMOND_1,
        Card::DIAMOND_2,
        Card::DIAMOND_3,
        Card::HEART_1,
        Card::HEART_2,
        Card::HEART_3,
    ];

    public static function id(
        string $id = null
    )
    {
        if (empty($id))
        {
            return self::$id;
        }

        return $id;
    }

    public static function set(
        array $overrideWith = [],
        bool  $replace      = false
    )
    {
        if ($replace)
        {
            return $overrideWith;
        }

        return array_merge(self::$set, $overrideWith);
    }
}
