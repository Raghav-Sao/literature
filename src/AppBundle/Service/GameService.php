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

        $hash = $this->redis->hgetall($id);

        if (empty($hash))
        {
            throw new NotFoundException('Game not found');
        }

        return new Game($id, $hash);
    }

    public function delete(
        Game $game
    )
    {
        $this->redis->del(
            $game->id,
            $game->u1,
            $game->u2,
            $game->u3,
            $game->u4
        );

        return $this;
    }

    public function getUser(
        string $id
    )
    {
        $set = $this->redis->smembers($id);

        if (empty($set))
        {
            throw new NotFoundException(
                'Cards not found for given user',
                ['id' => $id]
            );
        }
        else
        {
            return new User($id, $set);
        }
    }

    public function init(
        string $userId
    )
    {

        $id = Utility::newGameId();

        list($u1Cards, $u2Cards, $u3Cards, $u4Cards) = Utility::distributeCards();

        // TODO:
        // - Set other defaults

        $hash = $this->redis->hmset(
            $id,

            GameK::CREATED_AT,
            Utility::currentTimeStamp(),

            GameK::U1,
            $userId,

            GameK::STATUS,
            Status::INITIALIZED,

            GameK::NEXT_TURN,
            GameK::U1,

            GameK::U1_CARDS,
            implode(',', $u1Cards),

            GameK::U2_CARDS,
            implode(',', $u2Cards),

            GameK::U3_CARDS,
            implode(',', $u3Cards),

            GameK::U4_CARDS,
            implode(',', $u4Cards)
        );

        call_user_func_array(
            [$this->redis, 'sadd'],
            array_merge([$userId], $u1Cards)
        );

        return [
            $this->get($id),
            $this->getUser($userId),
        ];
    }

    public function getAndValidate(
        string $id,
        string $userId
    )
    {

        $game = $this->get($id);

        if ($game->isExpired())
        {
            $this->delete($game);

            throw new BadRequestException(
                'Game with given id is no longer active'
            );
        }

        if ($userId && $game->hasUser($userId) === false)
        {
            throw new BadRequestException(
                'You do not belong to game with given id'
            );
        }

        return [
            $game,
            $this->getUser($userId),
        ];
    }

    public function join(
        string $gameId,
        string $atSN,
        string $userId
    )
    {

        $game = $this->get($gameId);

        if ($game->isSNVacant($atSN) === false)
        {
            throw new BadRequestException('Invalid position to join as member');
        }

        $game->$atSN = $userId;
        if ($game->isAnySNVacant() === false)
        {
            $game->status = Status::ACTIVE;
        }

        $this->redis->hmset(
            $game->id,

            $atSN,
            $userId,

            GameK::STATUS,
            $game->status
        );

        call_user_func_array(
            [$this->redis, 'sadd'],
            array_merge([$userId], $game->getInitCardsBySN($atSN))
        );

        $payload = [
            'atSN' => $atSN,
            'game' => $game->toArray(),
        ];
        $this->pubSub->trigger(
            $game->id,
            Event::GAME_JOIN_ACTION,
            $payload
        );

        return [
            $game,
            $this->getUser($userId),
        ];
    }

    public function moveCard(
        Game   $game,
        string $card,
        string $fromUserId,
        string $toUserId
    )
    {
        if ($game->isActive() === false)
        {
            throw new BadRequestException('Game is not active');
        }

        if (Utility::isValidCard($card) === false)
        {
            throw new BadRequestException('Not a valid card');
        }

        if ($game->hasUser($fromUserId) === false)
        {
            throw new BadRequestException('Bad value for fromUserId, Does not exists');
        }

        if ($game->getNextTurnUserId() !== $toUserId)
        {
            throw new BadRequestException('It is not your turn to make a move');
        }

        if ($game->areTeam($fromUserId, $toUserId))
        {
            throw new BadRequestException('Bad value for fromUserId, You are partners');
        }

        $toUser   = $this->getUser($toUserId);

        if ($toUser->hasCard($card))
        {
            throw new BadRequestException('Bad value for card, You have it already');
        }

        if ($toUser->hasAtLeastOneCardOfType($card) === false)
        {
            $payload = [
                'success' => true,
                'game'    => $game->toArray(),
            ];

            $this->pubSub->trigger(
                $game->id,
                Event::GAME_MOVE_ACTION,
                $payload
            );

            throw new BadRequestException(
                'You do not have at least one card of that type. Invalid move'
            );
        }

        $fromUser = $this->getUser($fromUserId);

        // TODOs:
        // Check game completion and other stuff

        $success = false;

        if ($fromUser->hasCard($card) === false)
        {
            // Set game turn
            $fromUserSN = $game->getSNByUserId($fromUser->id);
            $this->redis->hmset(
                $game->id,

                GameK::NEXT_TURN,
                $fromUserSN
            );
            $game->nextTurn = $fromUserSN;

            $success = false;
        }
        else
        {
            // Move the card
            $this->redis->smove(
                $fromUser->id,
                $toUser->id,
                $card
            );
            $fromUser->removeCard($card);
            $toUser->addCard($card);

            $success = true;
        }

        $payload = [
            'success' => $success,
            'game'    => $game->toArray(),
        ];
        $this->pubSub->trigger(
            $game->id,
            Event::GAME_MOVE_ACTION,
            $payload
        );

        return [
            $success,
            $game,
            $toUser,
        ];
    }

    public function show(
        Game $game,
        User $user,
        string $cardType,
        string $cardRange
    )
    {
        list($success, $payload) = $this->attemptConsumeCardsOfTypeAndRange(
            $game,
            $game->getTeamForUserId($user->id),
            $cardType,
            $cardRange
        );

        if ($success)
        {
            return [$success, $payload, []];
        }

        list($success, $payload2) = $this->attemptConsumeCardsOfTypeAndRange(
            $game,
            $game->getTeamOppositeForUserId($user->id),
            $cardType,
            $cardRange,
            true
        );

        return [false, $payload, $payload2];
    }

    protected function attemptConsumeCardsOfTypeAndRange(
        Game   $game,
        string $team,
        string $cardType,
        string $cardRange,
        bool   $partial = false
    )
    {
        $userIds      = $game->getUserIdsOfTeam($team);

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

        // TODO:
        // - Update local model too
        // - Implement those game model methods too
        // - Add tests
        // - Refactor and refactor again :)

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
