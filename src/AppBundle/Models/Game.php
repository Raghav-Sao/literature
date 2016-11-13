<?php

namespace AppBundle\Models;

use AppBundle\Constants\Game\Status;

/**
 *
 */
class Game
{
    private $id;
    private $createdAt;
    private $status;
    private $nextTurn;

    // User serial numbers
    private $u1;
    private $u2;
    private $u3;
    private $u4;


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
    }

    /**
     *
     * @return boolean
     */
    public function isActive()
    {

        return ($this->status === Status::ACTIVE);
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
     *
     * @param string $userSN
     * @param string $userId
     *
     * @return boolean
     */
    public function isValidSNAgainstID($userSN, $userId)
    {

        return property_exists($this, $userSN) && $this->$userSN === $userId;
    }

    /**
     *
     * @param string $userSN
     *
     * @return null|string
     */
    public function getUserIdBySN($userSN)
    {
        if (property_exists($this, $userSN)) {

            return $this->$userSN;
        } else {
            // TODO: Should throw error?

            return null;
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
}
