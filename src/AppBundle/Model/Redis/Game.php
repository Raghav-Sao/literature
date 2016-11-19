<?php

namespace AppBundle\Model\Redis;

use AppBundle\Constant\Game\Status;
use AppBundle\Constant\Game\User;
use AppBundle\Exception\BadRequestException;

/**
 *
 */
class Game
{
    public $id;
    public $createdAt;
    public $status;
    public $nextTurn;

    // User serial numbers
    public $u1;
    public $u2;
    public $u3;
    public $u4;

    // Initial cards of all users
    public $u1Cards;
    public $u2Cards;
    public $u3Cards;
    public $u4Cards;

    /**
     *
     * @param string $id     - Game id
     * @param array  $params - Array data from redis hmset
     *
     * @return
     */
    public function __construct(
        string $id,
        array $params)
    {
        $this->id = $id;
        // Sets all attributes of the object
        foreach ($params as $key => $value) {
            $property = \AppBundle\Utility::camelizeLcFirst($key);
            $this->$property = $value;
        }

        // $this->team1 = [$this->u1, $this->u3];
        // $this->team2 = [$this->u2, $this->u4];
    }

    /**
     *
     * @return boolean
     */
    public function isExpired()
    {

        return ($this->status === Status::EXPIRED);
    }

    public function isUserSNVacant($userSN)
    {
        if (property_exists($this, $userSN) === false) {

            throw new BadRequestException("Invalid user serial number");
        }

        return ($this->$userSN == null);
    }

    public function isAnyUserSNVacant()
    {

        return ($this->u1 == null ||
                $this->u2 == null ||
                $this->u3 == null ||
                $this->u4 == null);
    }

    /**
     *
     * @return boolean
     */
    public function hasUser($userId)
    {

        return in_array(
            $userId,
            [
                $this->u1,
                $this->u2,
                $this->u3,
                $this->u4,
            ],
            true
        );
    }

    /**
     * @param string $userId1
     * @param string $userId2
     *
     * @return boolean
     */
    public function arePartners(
        string $userId1,
        string $userId2)
    {
        $team = [$this->u1, $this->u3];

        return in_array($userId1, $team, true) === in_array($userId2, $team, true);
    }

    /**
     *
     * @param string $userId
     *
     * @return null|string
     */
    public function getUserSNById($userId)
    {
        switch ($userId) {
            case $this->u1:
                return User::USER_1;
                break;
            case $this->u2:
                return User::USER_2;
                break;
            case $this->u3:
                return User::USER_3;
                break;
            case $this->u4:
                return User::USER_4;
                break;

            default:
                return null;
                break;
        }
    }

    /**
     * Gets user id with next turn
     *
     * @return string
     */
    public function getNextTurnUserId()
    {
        $nextTurnSN = $this->nextTurn;

        return $this->$nextTurnSN;
    }

    /**
     * @param string $userSN
     *
     * @return array
     */
    public function getInitialCardsByUserSN(
        string $userSN)
    {

        $attribute = sprintf("%sCards", $userSN);

        if (empty($this->$attribute)) {

            return [];
        } else {

            return explode(",", $this->$attribute);
        }
    }

    /**
     *
     * @return array
     */
    public function toArray()
    {

        return [
            "id"        => $this->id,
            "createdAt" => $this->createdAt,
            "status"    => $this->status,
            "u1"        => $this->u1,
            "u2"        => $this->u2,
            "u3"        => $this->u3,
            "u4"        => $this->u4,
        ];
    }



    ####################################################################
    // Setters

    /**
     * @param string $userSN
     * @param string $userId
     *
     * @return Game
     */
    public function setUserSN(
        string $userSN,
        string $userId)
    {

        $this->$userSN = $userId;

        return $this;
    }

    /**
     * @param string $userSN
     *
     * @return Game
     */
    public function setNextTurn(
        string $userSN)
    {
        $this->nextTurn = $userSN;

        return $this;
    }
}
