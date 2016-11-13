<?php

namespace AppBundle;

use AppBundle\Constants\Game\Game;

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

        return sprintf("%s_%s", Game::GAME_ID_PREFIX, self::randomString());
    }
}
