<?php

namespace AppBundle\Service\Result;

use AppBundle\Constant\SerializeGroup;
use AppBundle\Model\Redis\Game;
use AppBundle\Model\Redis\User;

class BaseServiceResult
{
    public $game;
    public $user;

    public $success;

    public $showResult;

    //
    // Group names to be used while serializing service results
    // Also, this cannot be overridden, so for now will contain
    // groups of all classes extending this class too.
    //

    private $groups = [
        SerializeGroup::DEFAULT   => [
                                        'game',
                                        'user'
                                     ],
        SerializeGroup::GAME_MOVE => [
                                        'success',
                                        'game',
                                        'user',
                                     ],
        SerializeGroup::GAME_SHOW => [
                                        'success',
                                        'game',
                                        'user',
                                        'showResult',
                                     ]
    ];

    public static function create(Game $game, User $user, array $params = [])
    {
        //
        // Creates instance of the result class, with default param signature
        //

        $instance = new self();

        $params['game'] = $game;
        $params['user'] = $user;

        $instance->fill($params);

        return $instance;
    }

    function __construct(array $params = [])
    {
        $this->fill($params);
    }

    public function serialize(string $group = SerializeGroup::DEFAULT)
    {
        $fields = $this->groups[$group];

        $result = [];

        foreach ($fields as $field)
        {
            $value = $this->$field;
            if (is_object($value))
            {
                $result[$field] = $value->toArray();
            }
            else
            {
                $result[$field] = $value;
            }
        }

        return $result;
    }

    protected function fill(array $params)
    {
        //
        // Takes associative array as arg, and fills array keys as property name
        // with array values as their value.
        //

        foreach ($params as $key => $value)
        {
            $this->$key = $value;
        }
    }
}
