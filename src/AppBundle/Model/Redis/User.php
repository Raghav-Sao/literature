<?php

namespace AppBundle\Model\Redis;

use AppBundle\Utility;

class User
{

    public $id;
    public $cards;

    public function __construct(
        string $id,
        array  $cards
    )
    {
        $this->id    = $id;
        $this->cards = $cards;
    }

    //
    // Getters

    public function hasCard(
        string $card
    )
    {
        return in_array($card, $this->cards, true);
    }

    public function hasAtLeastOneCardOfType(
        string $card
    )
    {
        $cardType  = Utility::getCardType($card);
        $cardRange = Utility::getCardRange($card);

        foreach ($this->cards as $value)
        {
            if ($cardType === Utility::getCardType($value)
                && $cardRange === Utility::getCardRange($value))
            {
                return true;
            }
        }

        return false;
    }

    public function toArray()
    {

        return [
            'id'    => $this->id,
            'cards' => $this->cards,
        ];
    }

    //
    // Setters

    public function addCard(
        string $card
    )
    {
        $this->cards[] = $card;

        return $this;
    }

    public function removeCard(
        string $card
    )
    {
        $key = array_search($card, $this->cards);

        if ($key !== false)
        {
            unset($this->cards[$key]);
        }

        return $this;
    }

    public function removeCards(
        array $cards
    )
    {
        foreach ($cards as $card)
        {
            $this->removeCard($card);
        }

        return $this;
    }
}
