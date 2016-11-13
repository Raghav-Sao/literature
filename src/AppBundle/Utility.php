<?php

namespace AppBundle;

use AppBundle\Constants\Game\Game;
use AppBundle\Constants\Game\Card;

/**
*
*/
class Utility
{

    /**
     * @param string $input
     * @param string $separator
     *
     * @return string
     */
    public static function camelize(
        string $input,
        string $separator = "_")
    {

        return str_replace($separator, '', ucwords($input, $separator));
    }

    /**
     * @param string $input
     * @param string $separator
     *
     * @return string
     */
    public static function camelizeLcFirst(
        string $input,
        string $separator = "_")
    {

        return lcfirst(self::camelize($input, $separator));
    }

    /**
     *
     *@return string
     */
    public static function randomString()
    {

        return md5(uniqid(rand(), true));
    }

    /**
     *
     * @return string
     */
    public static function newGameId()
    {

        return sprintf("%s%s", Game::GAME_ID_PREFIX, self::randomString());
    }

    /**
     *@return integer
     */
    public static function currentTimeStamp()
    {
        return time();
    }

    /**
     * @param string $card
     *
     * @return boolean
     */
    public static function isValidCard(
        string $card)
    {

        return in_array($card, Card::$allInGame, true);
    }

    /**
     * @param string $card
     *
     * @return string
     */
    public static function getCardType($card)
    {

        return substr($card, 0, 1);
    }

    /**
     * @param string $card
     *
     * @return string
     */
    public static function getCardValue($card)
    {

        return substr($card, 1);
    }

    /**
     * @param string $card
     *
     * @return string
     */
    public static function getCardRange($card)
    {
        $cardValue = self::getCardValue($card);

        if ($cardValue >= 1 && $cardValue <= 6) {

            return Card::LOWER_RANGE;
        } else {

            return Card::HIGHER_RANGE;
        }
    }

    /**
     * Returns set of 4 cards array, shuffled randomly
     *
     * @return array
     */
    public static function distributeCards()
    {
        $cards = Card::$allInGame;

        // Shuffle all cards randombly few times
        foreach (range(0, 10) as $key => $value) {
            shuffle($cards);
        }

        return array_chunk($cards, 12);
    }
}
