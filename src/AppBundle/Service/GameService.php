<?php

namespace AppBundle\Service;

use AppBundle\Model\Redis\Game;
use AppBundle\Model\Redis\User;
use AppBundle\Exception\NotFoundException;
use AppBundle\Exception\BadRequestException;
use AppBundle\Utility;
use AppBundle\Constant\Game\Game as GameK;
use AppBundle\Constant\Game\Status;
use AppBundle\Constant\Game\Event;
use AppBundle\Constant\Game\Card;

class GameService extends BaseService
{

    protected $redis;
    protected $pubSub;
    protected $knowledge;

    public function __construct(
                               $logger,
                               $redis,
        PubSub\PubSubInterface $pubSub,
        KnowledgeService       $knowledge
    )
    {
        parent::__construct($logger);

        $this->redis     = $redis;
        $this->pubSub    = $pubSub;
        $this->knowledge = $knowledge;
    }

    public function get(
        string $id
    )
    {
        // Gets game model by id

        $hash = $this->redis->hgetall($id);

        if (empty($hash))
        {
            throw new NotFoundException('Game not found');
        }

        return new Game($id, $hash);
    }

    public function getUser(
        string $id
    )
    {
        // Gets user model by id

        $set = $this->redis->smembers($id);

        if (empty($set))
        {
            $error = 'Cards not found for given user';
            $extra   = ['id' => $id];

            throw new NotFoundException($error, $extra);
        }

        return new User($id, $set);
    }

    public function getAndValidate(
        string $id,
        string $userId
    )
    {
        // Gets game model by given id and validates
        // if given userId belongs to the game.

        $game = $this->get($id);

        if ($game->isExpired())
        {
            $this->delete($game);

            $error = 'Game with given id is no longer active';

            throw new BadRequestException($error);
        }

        if ($userId && $game->hasUser($userId) === false)
        {
            $error = 'You do not belong to game with given id';

            throw new BadRequestException($error);
        }

        $user = $this->getUser($userId);

        return [$game, $user];
    }

    public function init(
        string $userId
    )
    {
        // Initializes a game with given userId as first member

        // - Creates game hash

        $id    = Utility::newGameId();
        $cards = Utility::distributeCards();

        $hash = $this->getInitHash($id, $userId, $cards);

        call_user_func_array([$this->redis, 'hmset'], $hash);

        // - Creates user's cards set

        $arg = array_merge([$userId], $cards[0]);

        call_user_func_array([$this->redis, 'sadd'], $arg);

        $game = $this->get($id);
        $user = $this->getUser($userId);

        return [$game, $user];
    }

    public function join(
        string $gameId,
        string $atSN,
        string $userId
    )
    {
        // Given userId joins the game at given position(serial number)

        $game = $this->get($gameId);

        if ($game->isSNVacant($atSN) === false)
        {
            throw new BadRequestException('Invalid position to join as member');
        }

        $game->$atSN = $userId;

        // If all 4 users joined then update game hash with required info

        if ($game->isAnySNVacant() === false)
        {
            $game->status            = Status::ACTIVE;
            $game->prevTurnTimeStamp = time();
        }

        // Update game hash with updated info

        $this->redis->hmset(
            $game->id,

            $atSN,
            $userId,

            GameK::STATUS,
            $game->status,

            GameK::PREV_TURN_TIMESTAMP,
            $game->prevTurnTimeStamp
        );

        // Creates init cards set for new users who joined

        $cards = $game->getInitCardsBySN($atSN);
        $arg   = array_merge([$userId], $cards);

        call_user_func_array([$this->redis, 'sadd'], $arg);

        // Publish this action

        $payload = [
            'atSN' => $atSN,
            'game' => $game->toArray(),
        ];
        $this->pubSub->trigger($game->id, Event::GAME_JOIN_ACTION, $payload);

        $user = $this->getUser($userId);

        return [$game, $user];
    }

    public function moveCard(
        Game   $game,
        string $card,
        string $fromUserId,
        string $toUserId
    )
    {
        // Attempts moving given card between given users of game

        $toUser   = $this->getUser($toUserId);
        $fromUser = $this->getUser($fromUserId);

        $this->validateMove($game, $card, $fromUser, $toUser);

        $success = true;

        if ($fromUser->hasCard($card))
        {
            $this->redis->smove($fromUser->id, $toUser->id, $card);

            $fromUser->removeCard($card);
            $toUser->addCard($card);

            $success = true;
        }
        else
        {
            // Else, update turns in the game

            $game->prevTurn = $game->nextTurn;

            $fromUserSN     = $game->getSNByUserId($fromUser->id);
            $game->nextTurn = $fromUserSN;

            $success = false;
        }

        // Update game has with nextTurn, prevTurn, prevTurnTimeStamp

        // TODO:
        // - Proper assignment of nextTurn
        //   If the user doesn't have any cards left then do not assign him
        //   as nextTurn user
        // - Proper publishing of such action/event

        $this->redis->hmset(
            $game->id,

            GameK::PREV_TURN,
            $game->prevTurn,

            GameK::PREV_TURN_TIMESTAMP,
            time(),

            GameK::NEXT_TURN,
            $game->nextTurn
        );

        // Publish this action

        $payload = [
            'success' => $success,
            'game'    => $game->toArray(),
        ];
        $this->pubSub->trigger($game->id, Event::GAME_MOVE_ACTION, $payload);

        return [$success, $game, $toUser];
    }

    public function show(
        Game $game,
        User $user,
        string $cardType,
        string $cardRange
    )
    {
        // Consumes cards of given type and range and set points

        // - Attemps consume for team of given user

        list($success, $payload1) = $this->showAndConsumeCardsByTeam(
            $game,
            $game->getTeam($user->id),
            $cardType,
            $cardRange
        );

        if ($success)
        {
            return [$success, $payload1, []];
        }

        // - Else, consumes for opposite team of given user

        list($success, $payload2) = $this->showAndConsumeCardsByTeam(
            $game,
            $game->getOppTeam($user->id),
            $cardType,
            $cardRange,
            true
        );

        // TODO:
        // - Check game completion: Complete game and publish the action

        return [false, $payload1, $payload2];
    }

    public function delete(
        Game $game
    )
    {
        // Deletes all redis keys for given game

        $this->redis->del(
            $game->id,
            $game->u1,
            $game->u2,
            $game->u3,
            $game->u4
        );

        return $this;
    }

    // ----------------------------------------------------------------------
    // Protected methods

    protected function getInitHash(
        string $id,     // Game id
        string $userId, // First user in game
        array  $cards   // 4 Set of cards for all users in game
    )
    {
        // Returns initial game hash, with userId as firt member

        $hash = [
            $id,

            GameK::CREATED_AT,
            Utility::currentTimeStamp(),

            GameK::STATUS,
            Status::INITIALIZED,

            // prevTurn, prevTurnTimeStamp null at this point

            GameK::NEXT_TURN,
            GameK::U1,

            GameK::U1,
            $userId,

            // u2, u3 & u4 null at this point

            GameK::U1_POINTS,
            0,

            GameK::U2_POINTS,
            0,

            GameK::U3_POINTS,
            0,

            GameK::U4_POINTS,
            0,

            GameK::U1_CARDS,
            implode(',', $cards[0]),

            GameK::U2_CARDS,
            implode(',', $cards[1]),

            GameK::U3_CARDS,
            implode(',', $cards[2]),

            GameK::U4_CARDS,
            implode(',', $cards[3]),
        ];

        return $hash;
    }

    protected function validateMove(
        Game   $game,
        string $card,
        User   $fromUser,
        User   $toUser
    )
    {
        // Does necessary validations for move action in a game

        if ($game->isActive() === false)
        {
            $error = 'Game is not active';

            throw new BadRequestException($error);
        }

        if (Utility::isValidCard($card) === false)
        {
            $error = 'Not a valid card';

            throw new BadRequestException($error);
        }

        if ($game->hasUser($fromUser->id) === false)
        {
            $error = 'Bad value for fromUserId, Does not exists';

            throw new BadRequestException($error);
        }

        if ($game->getNextTurnUserId() !== $toUser->id)
        {
            $error = 'It is not your turn to make a move';

            throw new BadRequestException($error);
        }

        if ($game->areTeam($fromUser->id, $toUser->id))
        {
            $error = 'Bad value for fromUserId, You are partners';

            throw new BadRequestException($error);
        }

        if ($toUser->hasCard($card))
        {
            $error = 'Bad value for card, You have it already';

            throw new BadRequestException($error);
        }

        if ($toUser->hasAtLeastOneCardOfType($card) === false)
        {
            $error = 'You do not have at least one card of that type. Invalid move';

            throw new BadRequestException($error);
        }
    }

    protected function showAndConsumeCardsByTeam(
        Game   $game,
        string $team,
        string $cardType,
        string $cardRange,
        bool   $partial = false
    )
    {
        // Flow:

        // - Gets team users
        // - Gets cards of given type and range for both users
        // - Assign scores to the users based in ratio

        // - If partial is set to false:
        //   - If total filtered cards count is not complete, return and dont' give
        //     any score
        // - If partial is set to true:
        //   - Even if total filtered cards count is not complete, assign scores in
        //     ration (Case: opposite team has the missing cards)

        // - Publishes the action with pre-defined payload

        $userIds      = $game->getTeamUsers($team);

        $user1        = $this->getUser($userIds[0]);
        $u1Cards      = Utility::filterCardsByTypeAndRange($cardType, $cardRange);
        $u1CardsCount = count($u1Cards);

        $u2Cards      = [];
        $u2CardsCount = 0;

        if ($u1CardsCount < Card::MAX_PER_TYPE_RANGE)
        {
            $user2        = $this->getUser($userIds[1]);
            $u2Cards      = Utility::filterCardsByTypeAndRange($cardType, $cardRange);
            $u2CardsCount = count($u2Cards);
        }

        $payload = [
            'game' => $game->toArray(),
            'u1'   => [
                'id'    => $user1->id,
                'cards' => $u1Cards,
            ],
            'u2'   => [
                'id'    => $user2->id,
                'cards' => $u2Cards,
            ],
        ];

        if ($u1CardsCount + $u2CardsCount < Card::MAX_PER_TYPE_RANGE &&
            $partial === false)
        {
            return [false, $payload];
        }

        $u1Points = 0;
        $u2Points = 0;

        if ($u1CardsCount > 0)
        {
            call_user_func_array(
                [$this->redis, 'srem'],
                array_merge([$user1->id], $u1Cards)
            );
            $user1->removeCards($u1Cards);

            $u1Points = $u1CardsCount / (float) Card::MAX_PER_TYPE_RANGE;
        }

        if ($u2CardsCount > 0)
        {
            call_user_func_array(
                [$this->redis, 'srem'],
                array_merge([$user2->id], $u1Cards)
            );
            $user2->removeCards($u1Cards);

            $u1Points = $u2CardsCount / (float) Card::MAX_PER_TYPE_RANGE;
        }

        $hash = [
            $game->id,

            GameK::U1_POINTS,
            $u1Points,

            GameK::U2_POINTS,
            $u2Points,
        ];

        $game->incrPoint($user1->id, $u1Points);
        $game->incrPoint($user2->id, $u2Points);

        call_user_func_array(
            [$this->redis, 'hincrby'],
            $hash
        );

        $this->pubSub->trigger(
            $game->id,
            Event::GAME_SHOW_ACTION,
            $payload
        );

        return [true, $payload];
    }
}
