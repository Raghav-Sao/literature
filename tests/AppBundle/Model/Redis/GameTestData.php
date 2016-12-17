<?php

namespace Tests\AppBundle\Model\Redis;

use AppBundle\Constant\Game\Status;

class GameTestData
{

    public static $id   = 'gid';

    public static $hash = [];

    public static function id(string $id = null)
    {
        if (empty($id))
        {
            return self::$id;
        }

        return $id;
    }

    public static function hash(array $override = [])
    {
        return array_merge(self::$hash, $override);
    }
}
