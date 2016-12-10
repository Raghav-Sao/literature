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

    // @codingStandardsIgnoreStart
    // User serial numbers
    public $u1;
    public $u2;
    public $u3;
    public $u4;

    public $u1Points;
    public $u2Points;
    public $u3Points;
    public $u4Points;

    // Initial cards of all users
    public $u1Cards;
    public $u2Cards;
    public $u3Cards;
    public $u4Cards;
    // @codingStandardsIgnoreStart

    public $teams;

    public $index;

    public function __construct(
        string $id,
        array  $params
    )
    {

        $this->id = $id;

        // Sets all attributes of the object
        foreach ($params as $key => $value)
        {
            $property        = Utility::camelizeLcFirst($key);
            $this->$property = $value;
        }

        // Sets teams
        $this->teams = [
            GameK::TEAM_1 => [$this->u1, $this->u3],
            GameK::TEAM_2 => [$this->u2, $this->u4],
        ];

        // Sets index
        $this->index = [
            $this->u1 => GameK::U1,
            $this->u2 => GameK::U2,
            $this->u3 => GameK::U3,
            $this->u4 => GameK::U4,
        ];
        $this->refreshAndCleanIndex();
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
        return in_array($userId, array_keys($this->index), true);
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
            'id'           => $this->id,
            'createdAt'    => (int) $this->createdAt,
            'status'       => $this->status,
            'prevTurn'     => $this->prevTurn,
            'prevTurnTime' => (int) $this->prevTurnTime,
            'nextTurn'     => $this->nextTurn,

            'u1'           => $this->u1,
            'u2'           => $this->u2,
            'u3'           => $this->u3,
            'u4'           => $this->u4,

            'u1Points'     => (int) $this->u1Points,
            'u2Points'     => (int) $this->u2Points,
            'u3Points'     => (int) $this->u3Points,
            'u4Points'     => (int) $this->u4Points,

            'teams'        => $this->teams,
            'index'        => $this->refreshAndCleanIndex(),
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
