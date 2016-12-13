<?php

namespace AppBundle\Model\Redis;

use AppBundle\Utility;
use AppBundle\Constant\Game\Status;
use AppBundle\Constant\Game\Card;
use AppBundle\Exception\BadRequestException;

class Game
{
    public $id;
    public $createdAt;
    public $status;
    public $prevTurn;
    public $prevTurnTime;
    public $nextTurn;

    public $usersCount       = 0;  // Count of users joined
    public $users            = []; // List of user ids
    public $usersWithNoCards = []; // List of users who have no cards left

    public $team0            = []; // List of ids in team0
    public $team1            = [];

    public $points           = []; // Associative list, (user id -> points)

    public $cards0           = []; // List of intial cards for first user joined
    public $cards1           = [];
    public $cards2           = [];
    public $cards3           = [];

    //
    // Following keys are stored in model and in redis in same way
    //

    public static $noOpKeys = [
        'id',
        'createdAt',
        'status',
        'prevTurn',
        'prevTurnTime',
        'nextTurn',
        'usersCount',
    ];

    //
    // Following keys are array in model but stored as comma separated
    // string in redis
    //

    public static $explodeOpKeys = [
        'users',
        'usersWithNoCards',
        'team0',
        'team1',
        'cards0',
        'cards1',
        'cards2',
        'cards3',
    ];

    public function __construct(string $id, array $params)
    {

        $this->id = $id;

        foreach ($params as $key => $value)
        {
            $this->setAttribute($key, $value);
        }
    }

    //
    // Getters

    public function isActive()
    {
        return ($this->status === Status::ACTIVE);
    }

    public function hasUser(string $userId)
    {
        return in_array($userId, $this->users, true);
    }

    public function hasCards(string $userId)
    {
        return (in_array($userId, $this->usersWithNoCards, true) === false);
    }

    public function canJoin(string $team)
    {
        //
        // Checks if a team can be joined by any user.
        // - Is valid team name?
        // - Is vacant?
        //

        if (in_array($team, ['team0', 'team1'], true) === false)
        {
            throw new BadRequestException('Invalid team name.');
        }

        if (count($this->$team) === 2)
        {
            throw new BadRequestException('Team not vacant.');
        }
    }

    public function areTeam(string $userId1, string $userId2)
    {
        $x = in_array($userId1, $this->team0, true);
        $y = in_array($userId2, $this->team0, true);

        return ($x === $y);
    }

    public function getInitCards()
    {
        //
        // Returns initial set of cards for new user joining
        //

        $key = 'cards' . ($this->usersCount - 1);

        return $this->$key;
    }

    public function getTeam(string $userId)
    {
        if (in_array($userId, $this->team0))
        {
            return 'team0';
        }
        else
        {
            return 'team1';
        }
    }

    public function getOppTeam(string $userId)
    {
        if (in_array($userId, $this->team0))
        {
            return 'team1';
        }
        else
        {
            return 'team0';
        }
    }

    public function getValidNextTurn()
    {
        if ($this->hasCards($this->nextTurn))
        {
            return $this->nextTurn;
        }

        $possibleNextTurn = array_diff($this->users, $this->usersWithNoCards);

        return current($possibleNextTurn);
    }

    public function allPointsMade()
    {
        return (array_sum($this->points) === Card::MAX_IN_GAME);
    }

    public function toArray()
    {

        return [
            'id'               => $this->id,
            'createdAt'        => (int) $this->createdAt,
            'status'           => $this->status,
            'prevTurn'         => $this->prevTurn,
            'prevTurnTime'     => (int) $this->prevTurnTime,
            'nextTurn'         => $this->nextTurn,

            'usersCount'       => (int) $this->usersCount,
            'users'            => $this->users,
            'usersWithNoCards' => $this->usersWithNoCards,

            'team0'            => $this->team0,
            'team1'            => $this->team1,

            'points'           => $this->points,
        ];
    }

    //
    // Setters

    public function refreshNoCardsList(array $users)
    {
        foreach ($users as $user)
        {
            if (count($user->cards) === 0)
            {
                $this->usersWithNoCards[] = $user->id;
            }
        }

        $this->usersWithNoCards = array_unique($this->usersWithNoCards);

        return $this;
    }

    //
    // Protected methods

    protected function setAttribute(string $key, string $value)
    {
        //
        // Method used to construct this model from redis hash
        //

        if (in_array($key, self::$noOpKeys, true))
        {
            $this->$key = $value;
        }
        else if (in_array($key, self::$explodeOpKeys, true))
        {
            if (empty($value) === false)
            {
                $this->$key = explode(',', $value);
            }
        }
        else if (preg_match('/(points_)(?P<userId>.*)/', $key, $matches))
        {
            $this->points[$matches['userId']] = (int) $value;
        }
    }
}
