<?php

namespace AppBundle\Service;

use AppBundle\Model;

use AppBundle\Exception\NotFoundException;
use AppBundle\Exception\BadRequestException;
use AppBundle\Utility;
use AppBundle\Constant;

/**
 *
 */
class Game extends BaseService
{

    protected $redis;
    protected $pubSub;
    protected $knowledge;

    /**
     * @param object                         $logger
     * @param object                         $redis
     * @param Service\PubSub\PubSubInterface $pubSub
     * @param Service\Knowledge              $knowledge
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
     * @param string $id
     *
     * @return null|Model\Redis\Game
     */
    public function fetchGameById(
        string $id)
    {
        $gameHash = $this->redis->hgetall($id);

        if (empty($gameHash)) {

            throw new NotFoundException("Game not found");
        } else {

            return new Model\Redis\Game($id, $gameHash);
        }
    }

    /**
     * @param string $id
     *
     * @return
     */
    public function delete(
        string $id)
    {
        // TODO
        // - Clean redis data for given gameId
    }

    /**
     * @param string $id
     *
     * @return null|Model\Redis\User
     */
    public function fetchUserById(
        string $id)
    {
        // Get user's cards set
        $cardsSet = $this->redis->smembers($id);

        if (empty($cardsSet)) {

            throw new NotFoundException(
                "Cards not found for given user",
                ["id" => $id]
            );
        } else {

            return new Model\Redis\User($id, $cardsSet);
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
            Constant\Game\Game::CREATED_AT,   Utility::currentTimeStamp(), 
            Constant\Game\User::USER_1,       $userId,
            Constant\Game\Game::STATUS,       Constant\Game\Status::INITIALIZED,
            Constant\Game\Game::NEXT_TURN,    Constant\Game\User::USER_1,
            Constant\Game\Game::U1_CARDS,     implode(",", $u1Cards),
            Constant\Game\Game::U2_CARDS,     implode(",", $u2Cards),
            Constant\Game\Game::U3_CARDS,     implode(",", $u3Cards),
            Constant\Game\Game::U4_CARDS,     implode(",", $u4Cards)
        );

        call_user_func_array(
            array($this->redis, "sadd"),
            array_merge([$userId], $u1Cards)
        );

        return [
            $this->fetchGameById($gameId),
            $this->fetchUserById($userId)
        ];
    }

    /*
     * Fetches game and user model by given id.
     *     Also, does some validation.
     *
     * @param string $gameId
     * @param string $userId
     *
     * @return
     */
    public function fetchByIdAndValidateAgainsUser(
        string $gameId,
        string $userId)
    {
        $game = $this->fetchGameById($gameId);

        if ($game->isExpired()) {

            $this->delete($game);

            throw new BadRequestException(
                "Game with given id is no longer active"
            );
        }

        if ($userId && $game->hasUser($userId) === false) {

            throw new BadRequestException(
                "You do not belong to game with given id"
            );
        }

        return [
            $game,
            $this->fetchUserById($userId)
        ];
    }

    /**
     *
     * @param Model\Redis\Game $game
     * @param string      $card
     * @param string      $fromUserId
     * @param string      $toUserId
     *
     * @return
     */
    public function moveCard(
        Model\Redis\Game $game,
        string $card,
        string $fromUserId,
        string $toUserId)
    {
        if ($game->status !== Constant\Game\Status::ACTIVE) {

            throw new BadRequestException("Game is not active");
        }

        if (Utility::isValidCard($card) === false) {

            throw new BadRequestException("Not a valid card");
        }

        if ($game->hasUser($fromUserId) === false) {

            throw new BadRequestException();
        }

        if ($game->arePartners($fromUserId, $toUserId) === true) {

            throw new BadRequestException();
        }

        if ($game->getNextTurnUserId() !== $toUserId) {

            throw new BadRequestException("It is not your turn to make a move");
        }

        $toUser   = $this->fetchUserById($toUserId);

        if ($toUser->hasAtLeastOneCardOfType($card) === false) {

            throw new BadRequestException(
                "You do not have at least one card of that type. Invalid move"
            );
        }

        $fromUser = $this->fetchUserById($fromUserId);

        // TODOs:
        // Publish response data too
        // Ensure $game & $user refreshed
        // Check game completion and other stuff

        $success = false;

        if ($fromUser->hasCard($card) === false) {

            // Set game turn
            $fromUserSN = $game->getUserSNById($fromUser->getId());
            $this->redis->hset(
                $game->id,
                Constant\Game\Game::NEXT_TURN,
                $fromUserSN
            );
            $game->setNextTurn($fromUserSN);

            $success = false;
        } else {

            // Move the card
            $this->redis->smove(
                $fromUser->getId(),
                $toUser->getId(),
                $card
            );
            $fromUser->removeCard($card);
            $toUser->addCard($card);

            $success = true;
        }

        return [
            $success,
            $game,
            $toUser
        ];
    }

    /**
     *
     * @param string $game
     * @param string $atSN
     * @param string $userId
     *
     * @return array
     *
     */
    public function joinGame(
        string $gameId,
        string $atSN,
        string $userId)
    {
        $game = $this->fetchGameById($gameId);

        if($game->isUserSNVacant($atSN) === false) {
            
            throw new BadRequestException("Invalid position to join as member");
        }

        $game->setUserSN($atSN, $userId);
        $game->status = $game->isAnyUserSNVacant() ? $game->status : Constant\Game\Status::ACTIVE;
        $this->redis->hMset(
            $game->id,
            $atSN,                      $userId,
            Constant\Game\Game::STATUS, $game->status
        );

        call_user_func_array(
            array($this->redis, "sadd"),
            array_merge([$userId], $game->getInitialCardsByUserSN($atSN))
        );

        $data["response"]    = [
            "game" => $game->toArray(),
            "atSN" => $atSN
        ];
        $data["message"] = "Succesfully Joined";
        $this->pubSub->trigger(
            $game->id,
            Constant\PubSub::NEWJOINING,
            $data
        );
        
        return [
            $game,
            $this->fetchUserById($userId)
        ];
    }

}
