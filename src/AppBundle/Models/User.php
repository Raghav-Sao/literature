<?php

namespace AppBundle\Models;

/**
 *
 */
class User
{

    private $id;
    private $cards;

    /**
     *
     * @param string $id
     * @param array  $cards
     *
     * @return
     */
    public function __construct(
        string $id,
        array  $cards)
    {

        $this->id    = $id;
        $this->cards = $cards;
    }


    public function toArray()
    {

        return [
            "id" => $this->id,
            "cards" => $this->cards,
        ];
    }
}
