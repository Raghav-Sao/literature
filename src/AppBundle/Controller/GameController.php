<?php

namespace AppBundle\Controller;

use AppBundle\Constant\Service;
use AppBundle\Constant\ContextKey;
use AppBundle\Utility;
use AppBundle\Controller\Response;

class GameController extends BaseController
{
    public function startAction()
    {
        $this->init();

        $this->checkIfUserActiveInAGame();

        list($game, $user) = $this->gameService->init($this->userId);

        $this->setContext(ContextKey::GAME_ID, $game->id);

        $res = [
            'game' => $game->toArray(),
            'user' => $user->toArray(),            
        ];

        return new Response\Ok($res);
    }

    public function indexAction()
    {
        $this->init();

        list($game, $user) = $this->gameService
                                  ->getAndValidate(
                                        $this->gameId,
                                        $this->userId
                                    );

        $res = [
            'game' => $game->toArray(),
            'user' => $user->toArray(),
        ];

        return new Response\Ok($res);
    }

    public function joinAction(
        string $gameId,
        string $atSN
    )
    {

        $this->init();

        $this->checkIfUserActiveInAGame();

        list($game, $user) = $this->gameService
                                  ->join(
                                        $gameId,
                                        $atSN,
                                        $this->userId
                                    );

        $this->setContext(ContextKey::GAME_ID, $game->id);

        $res = [
            'game' => $game->toArray(),
            'user' => $user->toArray(),
        ];

        return new Response\Ok($res);
    }

    public function moveFromAction(
        string $card,
        string $fromUserId
    )
    {

        $this->init();

        list($game, $user) = $this->gameService
                                  ->getAndValidate(
                                        $this->gameId,
                                        $this->userId
                                    );

        list($success, $game, $user) = $this->gameService
                                            ->moveCard(
                                                $game,
                                                $card,
                                                $fromUserId,
                                                $this->userId
                                            );

        $res = [
            'success' => $success,
            'game'    => $game->toArray(),
            'user'    => $user->toArray(),
        ];

        return new Response\Ok($res);
    }

    public function showAction(
      string $cardType,
      string $cardRange
    )
    {
        $this->init();

        list($game, $user) = $this->gameService
                                  ->fetchByIdAndValidateAgainsUser(
                                      $this->gameId,
                                      $this->userId
                                  );

        list($success, $game, $user) = $this->gameService
                                            ->show(
                                                $game,
                                                $user,
                                                $cardType,
                                                $cardRange
                                            );

        $res = [
            "success" => $success,
            "game"    => $game->toArray(),
            "user"    => $user->toArray(),
        ];

        return new Response\Ok($res);
    }

    public function deleteAction()
    {
        $this->init();

        list($game, $user) = $this->gameService
                                  ->getAndValidate(
                                        $this->gameId,
                                        $this->userId
                                    );

        $this->gameService->delete($game);

        $this->resetContext();

        $res = ['success' => true];

        return new Response\Ok($res);
    }
}
