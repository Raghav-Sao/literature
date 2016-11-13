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

        return in_array($card, Card::$all, true);
    }

    /**
     * @param string $card
     *
     * @return string
     */
    public static function getCardType($card)
    {

        return explode("_", $card)[0];
    }

    /**
     * @param string $card
     *
     * @return string
     */
    public static function getCardValue($card)
    {

        return explode("_", $card)[1];
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
}
