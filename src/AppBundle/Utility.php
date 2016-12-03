<?php

namespace AppBundle;

use AppBundle\Constant\Game\Game;
use AppBundle\Constant\Game\Card;

class Utility
{

    public static function camelize(
        string $input,
        string $separator = '_'
    )
    {
        return str_replace($separator, '', ucwords($input, $separator));
    }

    public static function camelizeLcFirst(
        string $input,
        string $separator = '_'
    )
    {
        return lcfirst(self::camelize($input, $separator));
    }

    public static function randomString()
    {
        return md5(uniqid(rand(), true));
    }

    public static function newGameId()
    {
        return Game::GAME_ID_PREFIX . self::randomString();
    }

    public static function currentTimeStamp()
    {
        return time();
    }

    public static function isAssocArray($v)
    {
        if (is_array($v) === false)
        {
            return false;
        }

        if (array() === $v)
        {
            return false;
        }

        return array_keys($v) !== range(0, count($v) - 1);
    }

    //
    // Card related Utility methods

    public static function isValidCard(
        string $card
    )
    {
        return in_array($card, Card::$allInGame, true);
    }

    public static function getCardType(
        string $card
    )
    {
        return substr($card, 0, 1);
    }

    public static function getCardValue(
        string $card
    )
    {
        return substr($card, 1);
    }

    public static function getCardRange(
        string $card
    )
    {
        $cardValue = self::getCardValue($card);

        if ($cardValue >= 1 && $cardValue <= 6)
        {
            return Card::LOWER_RANGE;
        }
        else
        {
            return Card::HIGHER_RANGE;
        }
    }

    public static function distributeCards()
    {
        $cards = Card::$allInGame;

        // Shuffle all cards randombly few times
        foreach (range(0, 10) as $key => $value)
        {
            shuffle($cards);
        }

        return array_chunk($cards, 12);
    }

    public static function getRandomCard(
        array & $cards
    )
    {
        return $cards[array_rand($cards, 1)];
    }

    /**
     * @param array  $cards
     * @param string $cardType
     * @param string $cardRange
     *
     * @return array
     */
    public static function filterCardsByTypeAndRange(
        array $cards,
        string $cardType,
        string $cardRange
    ) {

        return array_filter(
            $cards,
            function ($card) use ($cardType, $cardRange) {

                $isCardTypeSame  = ($cardType === self::getCardType($card));
                $isCardRangeSame = ($cardRange === self::getCardRange($card));

                return $isCardTypeSame && $isCardRangeSame;
            }
        );
    }
}
