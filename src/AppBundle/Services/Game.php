<?php

namespace AppBundle\Services;

use AppBundle\Models;

use AppBundle\Exceptions\NotFoundException;
use AppBundle\Exceptions\BadRequestException;

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

    /**
     *
     * @param Models\Game $game
     * @param string      $userId
     *
     * @return
     */
    public function validateGame(
        Models\Game $game,
        string $userId)
    {
        if (empty($game)) {

            throw new NotFoundException("Game with given id not found.");
        }

        if ($game->isActive() === false) {

            $this->delete($game);

            throw new NotFoundException("Game with given id is no longer active.");
        }

        if ($game->hasUser($userId) === false) {

            throw new BadRequestException("You do not belong to game with given id.");
        }
    }
}
