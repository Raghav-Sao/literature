<?php

namespace AppBundle\Services;

use AppBundle\Models;

use AppBundle\Exceptions\NotFoundException;
use AppBundle\Exceptions\BadRequestException;
use AppBundle\Utility;
use AppBundle\Constants;

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
        $redisUserCards = $this->redis->sMembers($userId);

        if (empty($redisUserCards)) {

            return null;
        } else {

            return new Models\User($userId, $redisUserCards);
        }
    }

    /**
     * @param string $userId
     *
     * @return null
     */
    public function initializeGame(
        string $userId)
    {
        $gameId = Utility::newGameId();
        list($u1Cards, $u2Cards, $u3Cards, $u4Cards) = Utility::distributeCards();
        $initializeGameResults = $this->redis->hmset(
            $gameId,
            Constants\Game\Game::CREATED_AT,   Utility::currentTimeStamp(), 
            Constants\Game\User::USER_1,       $userId,
            Constants\Game\Game::STATUS,       Constants\Game\Status::ACTIVE,
            Constants\Game\Game::NEXT_TURN,    Constants\Game\User::USER_1,
            Constants\Game\Game::U1_CARDS,     implode(",", $u1Cards),
            Constants\Game\Game::U2_CARDS,     implode(",", $u2Cards),
            Constants\Game\Game::U3_CARDS,     implode(",", $u3Cards),
            Constants\Game\Game::U4_CARDS,     implode(",", $u4Cards)
        );
        
        $initializeUserReuslt = $this->redis->sadd(
            $userId,
            $u1Cards
        );

        return [
            $this->fetchById($gameId),
            $this->fetchUserById($userId)
        ];
    }

    /*
     * @param Models\Game $game
     * @param string      $userId
     *
     * @return
     */
    public function validateGame(
        Models\Game $game,
        string $userId = null)
    {
        if (empty($game)) {

            throw new NotFoundException("Game with given id not found.");
        }

        if ($game->isActive() === false) {

            $this->delete($game);

            throw new NotFoundException("Game with given id is no longer active.");
        }

        if ($userId && $game->hasUser($userId) === false) {

            throw new BadRequestException("You do not belong to game with given id.");
        }
    }

    /**
     *
     * @param Models\Game      $game
     * @param string           $card
     * @param Models\User      $fromUser
     * @param Models\User      $toUser
     *
     * @return
     */
    public function moveCard(
        Models\Game $game,
        string $card,
        Models\User $fromUser,
        Models\User $toUser)
    {
        if (Utility::isValidCard($card) === false) {
            throw new BadRequestException("Not a valid card.");
        }

        if ($game->getNextTurnUserId() !== $toUser->getId()) {
            throw new BadRequestException("It is not your turn to make a move.");
        }

        if ($toUser->hasAtLeastOneCardOfType($card) === false) {
            throw new BadRequestException("You do not have at least one card of that type. Invalid move.");
        }

        // TODOs:
        // Publish response data too
        // Ensure $game & $user refreshed

        if ($fromUser->hasCard($card) === false) {
            $fromUserSN = $game->getUserSNById($fromUser->getId());
            $this->redis->hset(
                $game->getId(),
                Constants\Game\Game::NEXT_TURN,
                $fromUserSN
            );
            $game->setNextTurn($fromUserSN);

            return [
                false,
                "The other user does not have that card."
            ];
        }

        $this->redis->smove(
            $fromUser->getId(),
            $toUser->getId(),
            $card
        );
        $this->fromUser->removeCard($card);
        $this->toUser->addCard($card);

        return [
            true,
            "Move successful."
        ];
    }

    /**
     *
     *@param Models\Game game
     *@param string userSN
     *
     */
    public function joinMember(
        Models\Game $game,
        string $userSN,
        string $userId)
    {
        if(!$game->isUserSNVacant($userSN)) {
            
            throw new BadRequestException("Invalid position to join as member.");
        }

        $this->redis->hMset(
            $game->getId(),
            $userSN,
            $userId
            );

        $cards = $this->redis->hMget(
            $game->getId(),
            sprintf("%s_cards", $userSN)
        );

        $this->redis->sadd($userId, $cards);
        
        return array($this->fetchById($game->getId()), $this->fetchUserById($userId));
    }

}
