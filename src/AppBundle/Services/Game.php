<?php

namespace AppBundle\Services;

use AppBundle\Models;

/**
 *
 */
class Game extends BaseService
{

    protected $redis;
    protected $pubSub;
    protected $knowledge;

    /**
     * @param object                          $logger
     * @param object                          $redis
     * @param Services\PubSub\PubSubInterface $pubSub
     * @param Services\Knowledge              $knowledge
     *
     * @return
     */
    public function __construct(
        $logger,
        $redis,
        PubSub\PubSubInterface $pubSub,
        Knowledge $knowledge)
    {
        parent::__construct($logger);

        $this->redis     = $redis;
        $this->pubSub    = $pubSub;
        $this->knowledge = $knowledge;
    }

    /**
     * @param string $gameId
     *
     * @return null|Models\Game
     */
    public function fetchById(
        string $gameId)
    {
        $redisGameResults = $this->redis->hgetall($gameId);

        if (empty($redisGameResults)) {

            return null;
        } else {

            return new Models\Game($gameId, $redisGameResults);
        }
    }

    /**
     * @param Models\Game $game
     *
     * @return null
     */
    public function delete(
        Models\Game $game)
    {
        // TODO
        // - Clean redis data for given gameId
    }



    /**
     * @param string $userId
     *
     * @return null|Models\User
     */
    public function fetchUserById(
        string $userId)
    {
        $redisUserCards = $this->redis->smembers($userId);

        if (empty($redisUserCards)) {

            return null;
        } else {

            return new Models\User($userId, $redisUserCards);
        }
    }
}
