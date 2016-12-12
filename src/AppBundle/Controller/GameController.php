<?php

namespace AppBundle\Controller;

use AppBundle\Constant\Service;
use AppBundle\Constant\ContextKey;
use AppBundle\Constant\SerializeGroup as Group;
use AppBundle\Utility;
use AppBundle\Controller\Response;

class GameController extends BaseController
{
    public function startAction()
    {
        $this->init();

        $this->ensureNoGame();

        $result = $this->gameService->init($this->userId);

        $this->setContext(ContextKey::GAME_ID, $result->game->id);

        return new Response\Ok($result->serialize());
    }

    public function indexAction()
    {
        $this->init();

        $this->ensureGameAndUser();

        $result = $this->gameService->index($this->game, $this->user);


        return new Response\Ok($result->serialize());
    }

    public function joinAction(string $gameId, string $team)
    {

        $this->init();

        $this->ensureNoGame();

        $result = $this->gameService
                       ->join(
                            $gameId,
                            $team,
                            $this->userId
                        );

        $this->setContext(ContextKey::GAME_ID, $result->game->id);

        return new Response\Ok($result->serialize());
    }

    public function moveFromAction(string $card, string $fromUserId)
    {

        $this->init();

        $this->ensureGameAndUser();

        $moveResult = $this->gameService
                           ->moveCard(
                                $this->game,
                                $this->user,
                                $card,
                                $fromUserId
                            );

        return new Response\Ok($moveResult->serialize(Group::GAME_MOVE));
    }

    public function showAction(string $cardType, string $cardRange)
    {
        $this->init();

        $this->ensureGameAndUser();

        $showResult = $this->gameService
                            ->show(
                                $this->game,
                                $this->user,
                                $cardType,
                                $cardRange
                            );


        return new Response\Ok($showResult->serialize(Group::GAME_SHOW));
    }

    public function deleteAction()
    {
        $this->init();

        $this->ensureGameAndUser();

        $this->gameService->delete($this->game);

        $this->resetContext();

        $res = ['success' => true];

        return new Response\Ok($res);
    }
}
