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

    public function get(string $id)
    {
        //
        // Gets game model by id
        //

        $hash = $this->redis->hgetall($id);

        if (empty($hash))
        {
            throw new NotFoundException('Game not found.');
        }

        return new Game($id, $hash);
    }

    public function getUser(string $id)
    {
        //
        // Gets user model by id
        //

        $set = $this->redis->smembers($id);

        if (empty($set))
        {
            throw new NotFoundException('User\'s card set not found.');
        }

        return new User($id, $set);
    }

    public function index(Game $game, User $user)
    {
        return Result::create($game, $user);
    }

    public function init(string $userId)
    {
        //
        // Initializes a game with given userId as first member
        //

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

    public function join(string $gameId, string $team, string $userId)
    {
        //
        // Given user id joins the game in given team
        //

        $game = $this->get($gameId);

        $game->canJoin($team, $userId);

        $game->users[]         = $userId;
        $game->$team[]         = $userId;
        $game->points[$userId] = 0;

        $game->usersCount++;

        // If all 4 users joined then update game hash with required info

        if ($game->usersCount === 4)
        {
            $game->status       = Status::ACTIVE;
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

        $payload = [
            'team'   => $team,
            'userId' => $userId,
        ];
        $this->pubSub->trigger($game->id, Event::GAME_JOIN_ACTION, $payload);

        $user = $this->getUser($userId);

        return Result::create($game, $user);
    }

    public function moveCard(
        Game   $game,
        User   $toUser,
        string $card,
        string $fromUserId
    )
    {
        //
        // Attempts moving given card between given users of game
        //

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

            $game->nextTurn = $fromUser->id;

            $success = false;
        }

        // Update game hash with nextTurn, prevTurn, prevTurnTime

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
            'fromUserId' => $fromUser->id,
            'toUserId'   => $toUser->id,
            'card'       => $card,
            'success'    => $success,
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
        //
        // Consumes cards of given type and range and set points
        //

        // - Attemps consume for team of given user

        list($success, $result) = $this->showAndConsumeCardsByTeam(
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

        // Appends new result to existing one

        if ($result2)
        {
            foreach ($result2 as $value)
            {
                $result[] = $value;
            }
        }

        // Refresh models

        $game = $this->get($game->id);
        $user = $this->getUser($user->id);

        $result = ['success' => $success, 'showResult' => $result];

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

    public function delete(Game $game)
    {
        //
        // Deletes all redis keys associated with given game
        //

        $keys   = $game->users;
        $keys[] = $game->id;

        call_user_func_array([$this->redis, 'del'], $keys);

        return $this;
    }

    //
    // Protected methods

    protected function getInitHash(string $id, string $userId, array $cards)
    {
        //
        // Returns initial game hash, with userId as firt member
        //

        $hash = [
            $id,

            GameK::CREATED_AT,
            time(),

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

            // points_ for other users wont' exist at this point

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
        //
        // Does necessary validations for move action in a game
        //

        if (Utility::isValidCard($card) === false)
        {
            throw new BadRequestException('Not a valid card.');
        }

        if ($game->hasUser($fromUser->id) === false)
        {
            throw new BadRequestException('Bad value for fromUserId, Does not exists.');
        }

        if ($game->nextTurn !== $toUser->id)
        {
            throw new BadRequestException('It is not your turn to make a move.');
        }

        if ($game->areTeam($fromUser->id, $toUser->id))
        {
            throw new BadRequestException('Bad value for fromUserId, You are partners.');
        }

        if ($toUser->hasCard($card))
        {
            throw new BadRequestException('Bad value for card, You have it already.');
        }

        if ($toUser->hasAtLeastOneCardOfType($card) === false)
        {
            throw new BadRequestException('You do not have at least one card of that type. Invalid move.');
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

        $userIds      = $game->$team;

        $u1           = $this->getUser($userIds[0]);
        $u1Cards      = Utility::filterCardsByTypeAndRange($u1->cards, $cardType, $cardRange);
        $u1CardsCount = count($u1Cards);

        $u1Result  = [
            'id'         => $u1->id,
            'cardsShown' => $u1Cards,
            'points'     => 0,        // Points to be added, if win confirmed
        ];

        $u2           = $this->getUser($userIds[1]);
        $u2Cards      = Utility::filterCardsByTypeAndRange($u2->cards, $cardType, $cardRange);
        $u2CardsCount = count($u2Cards);

        $u2Result = [
            'id'         => $u2->id,
            'cardsShown' => $u2Cards,
            'points'     => 0,
        ];

        //
        // Remove shown cards from user's redis set
        //

        if ($u1CardsCount > 0)
        {
            call_user_func_array([$this->redis, 'srem'], array_merge([$u1->id], $u1Cards));
        }

        if ($u2CardsCount > 0)
        {
            call_user_func_array([$this->redis, 'srem'], array_merge([$u2->id], $u2Cards));
        }

        //
        // If both user combined couldn't make the full set, then proceed ahead
        // if 'partial' is set to true (will be the case when this flow gets
        // called for oposite team)
        //

        if ($u1CardsCount + $u2CardsCount < Card::MAX_PER_TYPE_RANGE &&
            $partial === false)
        {
            return [false, [$u1Result, $u2Result]];
        }

        //
        // Assigns points(equals to filtered cards count) to users and updates
        // redis keys
        //

        $u1Result['points'] = $u1CardsCount;
        $u2Result['points'] = $u2CardsCount;

        $this->redis->hincrby($game->id, 'points_' . $u1->id, $u1CardsCount);
        $this->redis->hincrby($game->id, 'points_' . $u2->id, $u2CardsCount);

        return [true, [$u1Result, $u2Result]];
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
            Event::GAME_OVER_ACTION
        );

        return $game;
    }
}
