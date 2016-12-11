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
use AppBundle\Service\Result\GameServiceResult as Result;

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

        if ($userId && $game->hasUser($userId) === false)
        {
            $error = 'You do not belong to game with given id';

            throw new BadRequestException($error);
        }

        $user = $this->getUser($userId);

        return Result::create($game, $user);
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

        return Result::create($game, $user);
    }

    public function join(
        string $gameId,
        string $team,
        string $userId
    )
    {
        // Given userId joins the game at given position(serial number)

        $game = $this->get($gameId);

        $game->canJoin($team, $userId);

        $game->users[]         = $userId;
        $game->$team[]         = $userId;
        $game->points[$userId] = 0;

        $game->usersCount++;

        // If all 4 users joined then update game hash with required info

        if ($game->usersCount === 4)
        {
            $game->status            = Status::ACTIVE;
            $game->prevTurnTime = time();
        }

        // Update game hash with updated info

        $this->redis->hmset(
            $game->id,

            GameK::USERS_COUNT,
            $game->usersCount,

            GameK::USERS,
            implode(',', $game->users),

            $team,
            implode(',', $game->$team),

            'points_' . $userId,
            0,

            GameK::STATUS,
            $game->status,

            GameK::PREV_TURN_TIME,
            $game->prevTurnTime
        );

        // Creates init cards set for new users who joined

        $cards = $game->getInitCards();
        $arg   = array_merge([$userId], $cards);

        call_user_func_array([$this->redis, 'sadd'], $arg);

        // Publish this action

        // $payload = [
        //     'atSN' => $atSN,
        //     'game' => $game->toArray(),
        // ];
        // $this->pubSub->trigger($game->id, Event::GAME_JOIN_ACTION, $payload);

        $user = $this->getUser($userId);

        return Result::create($game, $user);
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

        $game->prevTurn     = $game->nextTurn;
        $game->prevTurnTime = time();

        if ($fromUser->hasCard($card))
        {
            $this->redis->smove($fromUser->id, $toUser->id, $card);

            $fromUser->removeCard($card);
            $toUser->addCard($card);

            $success = true;
        }
        else
        {
            // Else, update nextTurn

            $fromUserSN     = $game->getSNByUserId($fromUser->id);
            $game->nextTurn = $fromUserSN;

            $success = false;
        }

        // Update game hash with nextTurn, prevTurn, prevTurnTime

        // TODO:
        // - Proper assignment of nextTurn
        //   If the user doesn't have any cards left then do not assign him
        //   as nextTurn user
        // - Proper publishing of such action/event

        $this->redis->hmset(
            $game->id,

            GameK::PREV_TURN,
            $game->prevTurn,

            GameK::PREV_TURN_TIME,
            $game->prevTurnTime,

            GameK::NEXT_TURN,
            $game->nextTurn
        );

        // Publish this action

        $payload = [
            'success' => $success,
            'game'    => $game->toArray(),
        ];
        $this->pubSub->trigger($game->id, Event::GAME_MOVE_ACTION, $payload);

        $params = ['success' => $success];

        return Result::create($game, $toUser, $params);
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

        list($success, $result1) = $this->showAndConsumeCardsByTeam(
            $game,
            $game->getTeam($user->id),
            $cardType,
            $cardRange
        );

        $result2 = [];

        // - Else, consumes for opposite team of given user

        if ($success === false)
        {

            list($temp, $result2) = $this->showAndConsumeCardsByTeam(
                $game,
                $game->getOppTeam($user->id),
                $cardType,
                $cardRange,
                true
            );
        }

        // Refresh user model

        $user = $this->getUser($user->id);

        $result = array_merge(['success' => $success], $result1, $result2);

        // Publish event

        $this->pubSub->trigger(
            $game->id,
            Event::GAME_SHOW_ACTION,
            $result
        );

        // Check if game is complete

        $game = $this->checkAndProcessGameCompletion($game);

        return Result::create($game, $user, $result);
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

            // prevTurn, prevTurnTime null at this point

            GameK::NEXT_TURN,
            $userId,

            GameK::USERS_COUNT,
            1,

            GameK::USERS,
            $userId,

            GameK::TEAM0,
            $userId,

            // team1 null at this point

            'points_' . $userId,
            0,

            GameK::CARDS0,
            implode(',', $cards[0]),

            GameK::CARDS1,
            implode(',', $cards[1]),

            GameK::CARDS2,
            implode(',', $cards[2]),

            GameK::CARDS3,
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
        //
        // Get both users of the given team
        // Create default result array with cards shown and points made
        //

        $userIds      = $game->getTeamUsers($team);

        $user1        = $this->getUser($userIds[0]);
        $user1SN      = $game->getSNByUserId($user1->id);
        $u1Cards      = Utility::filterCardsByTypeAndRange($user1->cards, $cardType, $cardRange);
        $u1CardsCount = count($u1Cards);

        $user1Result  = [
            'id'         => $user1->id,
            'cardsShown' => $u1Cards,
            'points'     => $u1CardsCount,
        ];

        $user2        = $this->getUser($userIds[1]);
        $user2SN      = $game->getSNByUserId($user2->id);
        $u2Cards      = Utility::filterCardsByTypeAndRange($user2->cards, $cardType, $cardRange);
        $u2CardsCount = count($u2Cards);

        $user2Result = [
            'id'         => $user2->id,
            'cardsShown' => $u2Cards,
            'points'     => $u2CardsCount,
        ];

        $result = [
            $user1SN  => $user1Result,
            $user2SN  => $user2Result,
        ];

        //
        // If both user combined couldn't make the full set, then proceed ahead
        // if 'partial' is set to true (will be the case when this flow gets
        // called for oposite team)
        //

        if ($u1CardsCount + $u2CardsCount < Card::MAX_PER_TYPE_RANGE &&
            $partial === false)
        {
            return [false, $result];
        }

        //
        // Assigns points(equals to filtered cards count) to users and updates
        // redis keys and model
        //

        if ($u1CardsCount > 0)
        {
            call_user_func_array(
                [$this->redis, 'srem'],
                array_merge([$user1->id], $u1Cards)
            );
            $user1->removeCards($u1Cards);
        }

        if ($u2CardsCount > 0)
        {
            call_user_func_array(
                [$this->redis, 'srem'],
                array_merge([$user2->id], $u2Cards)
            );
            $user2->removeCards($u2Cards);
        }

        $this->redis->hincrby($game->id, $user1SN . '_points', $u1CardsCount);
        $this->redis->hincrby($game->id, $user2SN . '_points', $u2CardsCount);

        $game->incrPoint($user1->id, $u1CardsCount);
        $game->incrPoint($user2->id, $u2CardsCount);

        return [true, $result];
    }

    protected function checkAndProcessGameCompletion(
        Game $game
    )
    {
        //
        // If all points in game are made, mark the game's status as OVER.
        // Publish the same message in proper format.
        //

        if ($game->allPointsMade() === false)
        {
            return $game;
        }

        //
        // Sets game status to OVER
        //

        $game->status = Status::OVER;

        $hash = [
            $game->id,

            GameK::STATUS,
            $game->status,
        ];

        call_user_func_array([$this->redis, 'hmset'], $hash);

        $this->pubSub->trigger(
            $game->id,
            Event::GAME_OVER_ACTION,
            $game->toArray()
        );

        return $game;
    }
}
