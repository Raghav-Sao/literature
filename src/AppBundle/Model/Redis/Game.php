<?php

namespace AppBundle\Model\Redis;

use AppBundle\Utility;
use AppBundle\Constant\Game\Status;
use AppBundle\Constant\Game\Card;
use AppBundle\Constant\Game\Game as GameK;
use AppBundle\Exception\BadRequestException;

class Game
{
    public $id;
    public $createdAt;
    public $status;
    public $prevTurn;
    public $prevTurnTime;
    public $nextTurn;

    public $usersCount = 0;  // Count of users joined
    public $users      = []; // List of user ids

    public $team0      = []; // List of ids in team0
    public $team1      = [];

    public $points     = []; // Associative list, (user id -> points)

    public $cards0     = []; // List of intial cards for first user joined
    public $cards1     = [];
    public $cards2     = [];
    public $cards3     = [];

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

    public function canJoin(string $team)
    {
        //
        // Checks if a team can be joined by any user.
        // - Is valid team name?
        // - Is vacant?
        //

        if (in_array($team, [GameK::TEAM0, GameK::TEAM1], true) === false)
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
            return GameK::TEAM0;
        }
        else
        {
            return GameK::TEAM1;
        }
    }

    public function getOppTeam(string $userId)
    {
        if ($this->getTeam($userId) === GameK::TEAM0)
        {
            return GameK::TEAM1;
        }
        else
        {
            return GameK::TEAM0;
        }
    }

    public function allPointsMade()
    {
        return (array_sum($this->points) === Card::MAX_IN_GAME);
    }

    public function toArray()
    {

        return [
            GameK::ID             => $this->id,
            GameK::CREATED_AT     => (int) $this->createdAt,
            GameK::STATUS         => $this->status,
            GameK::PREV_TURN      => $this->prevTurn,
            GameK::PREV_TURN_TIME => (int) $this->prevTurnTime,
            GameK::NEXT_TURN      => $this->nextTurn,
            
            GameK::USERS_COUNT    => (int) $this->usersCount,
            GameK::USERS          => $this->users,
            
            GameK::TEAM0          => $this->team0,
            GameK::TEAM1          => $this->team1,
            
            GameK::POINTS         => $this->points,
        ];
    }

    //
    // Protected methods

    protected function setAttribute(string $key, string $value)
    {
        //
        // Method used to construct this model from redis hash
        //

        if (in_array($key, GameK::$noOpKeys, true))
        {
            $this->$key = $value;
        }
        else if (in_array($key, GameK::$explodeOpKeys, true))
        {
            $this->$key = explode(',', $value);
        }
        else if (preg_match('/(points_)(?P<userId>.*)/', $key, $matches))
        {
            $this->points[$matches['userId']] = (int) $value;
        }
    }
}
