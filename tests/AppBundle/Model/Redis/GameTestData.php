<?php

namespace Tests\AppBundle\Model\Redis;

use AppBundle\Constant\Game\Status;

class GameTestData
{

    public static $id   = 'gid';

    public static $hash = [

        'created_at'     => '1479581289',
        'status'         => Status::INITIALIZED,
        'prev_turn'      => null,
        'prev_turn_time' => null,
        'next_turn'      => 'u1',

        'u1'             => 'uid1',
        'u2'             => 'uid2',
        'u3'             => 'uid3',
        'u4'             => null,

        'u1_points'      => 0,
        'u2_points'      => 0,
        'u3_points'      => 0,
        'u4_points'      => 0,

        'u1_cards'       => 'c1,c2,c3,s1,s3,s3,d1,d2,d3,h1,h2,h3',
        'u2_cards'       => 'c4,c5,c6,s4,s5,s6,d4,d5,d6,h4,h5,h6',
        'u3_cards'       => 'c8,c9,c10,s8,s9,s10,d8,d9,d10,h8,h9,h10',
        // 'u4_cards'       => 'c11,c12,c13,s11,s12,s13,d11,d12,d13,h11,h12,h13',
        'u4_cards'       => '',
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

    public static function hash(
        array $overrideWith = []
    )
    {
        return array_merge(self::$hash, $overrideWith);
    }

    public static function activeHash(
        array $overrideWith = []
    )
    {
        $hash = self::$hash;

        $hash['u4_cards'] = 'c11,c12,c13,s11,s12,s13,d11,d12,d13,h11,h12,h13';
        $hash['u4']       = 'uid4';
        $hash['status']   = Status::ACTIVE;

        return array_merge($hash, $overrideWith);
    }
}
