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

    // User serial numbers
    private $u1;
    private $u2;
    private $u3;
    private $u4;


    /**
     *
     * @param array @params - Array data from redis hmset
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
            [
                $this->u1,
                $this->u2,
                $this->u3,
                $this->u4,
            ],
            $userId
        );
    }

    /**
     *
     * @return array
     */
    public function toArray()
    {

        return [
            "id"        => $id,
            "createdAt" => $createdAt,
            "status"    => $status,
            "u1"        => $u1,
            "u2"        => $u2,
            "u3"        => $u3,
            "u4"        => $u4,
        ];
    }
}
