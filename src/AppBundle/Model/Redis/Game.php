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

    public $usersCount = 0;
    public $users      = [];

    public $team0      = [];
    public $team1      = [];

    public $points     = [];

    public $cards0     = [];
    public $cards1     = [];
    public $cards2     = [];
    public $cards3     = [];

    public function __construct(
        string $id,
        array  $params
    )
    {

        $this->id = $id;

        foreach ($params as $key => $value)
        {
            $this->setAttribute($key, $value);
        }
    }

    protected function setAttribute(
        string $key,
        string $value
    )
    {
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

    //
    // Getters

    public function isActive()
    {
        return ($this->status === Status::ACTIVE);
    }

    public function isExpired()
    {
        return ($this->status === Status::EXPIRED);
    }

    public function isNotExpired()
    {
        return !($this->isExpired());
    }

    public function isSNVacant(
        string $userSN
    )
    {
        // Checks if given serial number is vacant for new users

        if (property_exists($this, $userSN) === false)
        {
            throw new BadRequestException('Invalid user serial number');
        }

        return ($this->$userSN == null);
    }

    public function isAnySNVacant()
    {
        // Checks if any serial number is vacant at all in the game

        return ($this->u1 == null ||
                $this->u2 == null ||
                $this->u3 == null ||
                $this->u4 == null);
    }

    public function hasUser(
        string $userId
    )
    {
        return in_array($userId, $this->users, true);
    }

    public function areTeam(
        string $userId1,
        string $userId2
    )
    {

        // Checks if given two users are team

        $x = in_array($userId1, $this->teams[GameK::TEAM_1], true);
        $y = in_array($userId2, $this->teams[GameK::TEAM_1], true);

        return ($x === $y);
    }

    public function getSNByUserId(
        string $userId
    )
    {
        return $this->index[$userId];
    }

    public function getNextTurnUserId()
    {
        $nextTurnSN = $this->nextTurn;

        return $this->$nextTurnSN;
    }

    public function getInitCardsBySN(
        string $userSN
    )
    {

        $attribute = $userSN . 'Cards';

        if (empty($this->$attribute))
        {
            return [];
        }
        else
        {
            return explode(',', $this->$attribute);
        }
    }

    public function getTeamUsers(
        string $team
    )
    {
        return $this->teams[$team];
    }

    public function getTeam(
        string $userId
    )
    {
        // Returns team of given user id

        if (in_array($userId, $this->teams[GameK::TEAM_1]))
        {
            return GameK::TEAM_1;
        }
        else
        {
            return GameK::TEAM_2;
        }
    }

    public function getOppTeam(
        string $userId
    )
    {
        // Returns opposite team for given user id

        if (in_array($userId, $this->teams[GameK::TEAM_1]))
        {
            return GameK::TEAM_2;
        }
        else
        {
            return GameK::TEAM_1;
        }
    }

    public function allPointsMade()
    {
        //
        // Returns true if all points has been made in this game
        //

        $sum = $this->u1Points +
               $this->u2Points +
               $this->u3Points +
               $this->u4Points;

        return ($sum === Card::MAX_IN_GAME);
    }

    public function refreshAndCleanIndex()
    {
        $this->index = array_filter(
            $this->index,
            function($key) {
                return (empty($key) === false);
            },
            ARRAY_FILTER_USE_KEY
        );

        return $this->index;
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
            
            GameK::USERS_COUNT    => $this->usersCount,
            GameK::USERS          => $this->users,
            
            GameK::TEAM0          => $this->team0,
            GameK::TEAM1          => $this->team1,
            
            GameK::POINTS         => $this->points,
        ];
    }

    //
    // Setters

    public function incrPoint(
        string $userId,
        int    $point
    )
    {
        // Increments points by given value for given user

        $property = $this->getSNByUserId($userId) . "Points";

        $this->$property += $point;

        return $this;
    }
}
