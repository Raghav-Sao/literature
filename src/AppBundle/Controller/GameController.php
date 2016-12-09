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

        $this->throwIfUserActiveInAnotherGame();

        $result = $this->gameService->init($this->userId);

        $this->setContext(ContextKey::GAME_ID, $result->game->id);

        return new Response\Ok($result->serialize());
    }

    public function indexAction()
    {
        $this->init();

        $result = $this->gameService
                       ->getAndValidate(
                            $this->gameId,
                            $this->userId
                        );

        return new Response\Ok($result->serialize());
    }

    public function joinAction(
        string $gameId,
        string $atSN
    )
    {

        $this->init();

        $this->throwIfUserActiveInAnotherGame();

        $result = $this->gameService
                       ->join(
                            $gameId,
                            $atSN,
                            $this->userId
                        );

        $this->setContext(ContextKey::GAME_ID, $game->id);

        return new Response\Ok($result->serialize());
    }

    public function moveFromAction(
        string $card,
        string $fromUserId
    )
    {

        $this->init();

        $result = $this->gameService
                       ->getAndValidate(
                            $this->gameId,
                            $this->userId
                        );

        $moveResult = $this->gameService
                           ->moveCard(
                                $result->game,
                                $card,
                                $fromUserId,
                                $this->userId
                            );

        return new Response\Ok($moveResult->serialize(Group::MOVE));
    }

    public function showAction(
      string $cardType,
      string $cardRange
    )
    {
        $this->init();

        $result = $this->gameService
                       ->getAndValidate(
                            $this->gameId,
                            $this->userId
                        );

        list($success, $payload1, $payload2) = $this->gameService
                                                    ->show(
                                                        $result->game,
                                                        $result->user,
                                                        $cardType,
                                                        $cardRange
                                                    );

        $res = [
            'success'  => $success,
            'game'     => $game->toArray(),
            'user'     => $user->toArray(),
            'payload1' => $payload1,
            'payload2' => $payload2
        ];

        return new Response\Ok($res);
    }

    public function deleteAction()
    {
        $this->init();

        $result = $this->gameService
                       ->getAndValidate(
                            $this->gameId,
                            $this->userId
                        );

        $this->gameService->delete($result->game);

        $this->resetContext();

        $res = ['success' => true];

        return new Response\Ok($res);
    }
}
