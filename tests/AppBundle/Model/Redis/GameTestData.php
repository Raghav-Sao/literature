<?php

namespace Tests\AppBundle\Model\Redis;

use AppBundle\Constant\Game;

class GameTestData
{

    public static $id              = 'g_1234567890';

    public static $defaultGameHash = [

        'created_at' => '1479581289',
        'status'     => Game\Status::INITIALIZED,
        'next_turn'  => 'u1',

        'u1'         => 'u_1111111111',
        'u2'         => null,
        'u3'         => null,
        'u4'         => null,

        'u1_cards'   => '',
        'u2_cards'   => '',
        'u3_cards'   => '',
        'u4_cards'   => '',
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

    public static function getGameHash(
        array $overrideWith = []
    )
    {
        self::$defaultGameHash['u1_cards'] = implode(',', [Game\Card::CLUB_1, Game\Card::CLUB_2]);

        return array_merge(self::$defaultGameHash, $overrideWith);
    }
}
