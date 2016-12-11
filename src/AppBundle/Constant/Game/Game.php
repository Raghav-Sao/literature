<?php

namespace AppBundle\Constant\Game;

class Game
{
    const GAME_ID_PREFIX = 'g_';

    const ID             = 'id';
    const CREATED_AT     = 'createdAt';
    const STATUS         = 'status';
    const PREV_TURN      = 'prevTurn';
    const PREV_TURN_TIME = 'prevTurnTime';
    const NEXT_TURN      = 'nextTurn';

    const USERS_COUNT    = 'usersCount';
    const USERS          = 'users';

    const TEAM0          = 'team0';
    const TEAM1          = 'team1';

    const POINTS         = 'points';

    const CARDS0         = 'cards0';
    const CARDS1         = 'cards1';
    const CARDS2         = 'cards2';
    const CARDS3         = 'cards3';

    //
    // Following keys are stored in model and in redis in same way
    //

    public static $noOpKeys = [
        self::ID,
        self::CREATED_AT,
        self::STATUS,
        self::PREV_TURN,
        self::PREV_TURN_TIME,
        self::NEXT_TURN,
        self::USERS_COUNT,
    ];

    //
    // Following keys are array in model but stored as comma separated
    // string in redis
    //

    public static $explodeOpKeys = [
        self::USERS,
        self::TEAM0,
        self::TEAM1,
        self::CARDS0,
        self::CARDS1,
        self::CARDS2,
        self::CARDS3,
    ];
}
