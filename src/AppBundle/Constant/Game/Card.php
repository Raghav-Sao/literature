<?php

namespace AppBundle\Constant\Game;

class Card
{
    const CLUB_TYPE            = 'c';
    const SPADE_TYPE           = 's';
    const DIAMOND_TYPE         = 'd';
    const HEART_TYPE           = 'h';
    
    const LOWER_RANGE          = 'lower';
    const HIGHER_RANGE         = 'higher';
    
    const COUNT_PER_TYPE_RANGE = 6;
    
    const CLUB_1               = 'c1';
    const CLUB_2               = 'c2';
    const CLUB_3               = 'c3';
    const CLUB_4               = 'c4';
    const CLUB_5               = 'c5';
    const CLUB_6               = 'c6';
    const CLUB_7               = 'c7';
    const CLUB_8               = 'c8';
    const CLUB_9               = 'c9';
    const CLUB_10              = 'c10';
    const CLUB_11              = 'c11';
    const CLUB_12              = 'c12';
    const CLUB_13              = 'c13';
    
    const SPADE_1              = 's1';
    const SPADE_2              = 's2';
    const SPADE_3              = 's3';
    const SPADE_4              = 's4';
    const SPADE_5              = 's5';
    const SPADE_6              = 's6';
    const SPADE_7              = 's7';
    const SPADE_8              = 's8';
    const SPADE_9              = 's9';
    const SPADE_10             = 's10';
    const SPADE_11             = 's11';
    const SPADE_12             = 's12';
    const SPADE_13             = 's13';
    
    const DIAMOND_1            = 'd1';
    const DIAMOND_2            = 'd2';
    const DIAMOND_3            = 'd3';
    const DIAMOND_4            = 'd4';
    const DIAMOND_5            = 'd5';
    const DIAMOND_6            = 'd6';
    const DIAMOND_7            = 'd7';
    const DIAMOND_8            = 'd8';
    const DIAMOND_9            = 'd9';
    const DIAMOND_10           = 'd10';
    const DIAMOND_11           = 'd11';
    const DIAMOND_12           = 'd12';
    const DIAMOND_13           = 'd13';
    
    
    const HEART_1              = 'h1';
    const HEART_2              = 'h2';
    const HEART_3              = 'h3';
    const HEART_4              = 'h4';
    const HEART_5              = 'h5';
    const HEART_6              = 'h6';
    const HEART_7              = 'h7';
    const HEART_8              = 'h8';
    const HEART_9              = 'h9';
    const HEART_10             = 'h10';
    const HEART_11             = 'h11';
    const HEART_12             = 'h12';
    const HEART_13             = 'h13';

    // Doesn't include card 7
    public static $allInGame = [
        self::CLUB_1,
        self::CLUB_2,
        self::CLUB_3,
        self::CLUB_4,
        self::CLUB_5,
        self::CLUB_6,
        self::CLUB_8,
        self::CLUB_9,
        self::CLUB_10,
        self::CLUB_11,
        self::CLUB_12,
        self::CLUB_13,

        self::SPADE_1,
        self::SPADE_2,
        self::SPADE_3,
        self::SPADE_4,
        self::SPADE_5,
        self::SPADE_6,
        self::SPADE_8,
        self::SPADE_9,
        self::SPADE_10,
        self::SPADE_11,
        self::SPADE_12,
        self::SPADE_13,

        self::DIAMOND_1,
        self::DIAMOND_2,
        self::DIAMOND_3,
        self::DIAMOND_4,
        self::DIAMOND_5,
        self::DIAMOND_6,
        self::DIAMOND_8,
        self::DIAMOND_9,
        self::DIAMOND_10,
        self::DIAMOND_11,
        self::DIAMOND_12,
        self::DIAMOND_13,

        self::HEART_1,
        self::HEART_2,
        self::HEART_3,
        self::HEART_4,
        self::HEART_5,
        self::HEART_6,
        self::HEART_8,
        self::HEART_9,
        self::HEART_10,
        self::HEART_11,
        self::HEART_12,
        self::HEART_13,
    ];
}
