<?php

namespace AppBundle;

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
}
