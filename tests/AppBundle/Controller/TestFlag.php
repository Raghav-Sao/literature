<?php

namespace Tests\AppBundle\Controller;

class TestFlag
{
    //
    // This is extensible and if required will have very awesome features.
    // But for now it's okay.
    //

    const SOMETHING = "SOMETHING";

    public static function isDefined(string $value)
    {
        return defined('self::' . $value);
    }
}
