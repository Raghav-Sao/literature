<?php

namespace Tests\AppBundle\Controller;

/**
 * TODO:
 * - This is extensible and if required will have
 *   very awesome features. But for now it's okay.
 */
class TestFlag
{
    const SOMETHING = "SOMETHING";

    /**
     * Is given value defined as constant?
     *
     * @param string $value
     *
     * @return boolean
     */
    public static function isDefined(string $value)
    {
        return defined('self::' . $value);
    }
}
