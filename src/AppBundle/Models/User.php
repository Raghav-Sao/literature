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

    /**
     * @return string
     */
    public function getId()
    {

        return $this->id;
    }

    /**
     *
     * @param string $card
     *
     * @return boolean
     */
    public function hasAtLeastOneOfType(
        string $card)
    {
        // TODO: Implment this

        return true;
    }

    /**
     *
     * @return array
     */
    public function toArray()
    {

        return [
            "id" => $this->id,
            "cards" => $this->cards,
        ];
    }
}
