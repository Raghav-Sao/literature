<?php

namespace AppBundle\Controller;

use AppBundle\Constant\Service;
use AppBundle\Constant\ContextKey;
use AppBundle\Utility;

use AppBundle\Controller\Response;

/**
 *
 */
class GameController extends BaseController
{

    /**
     *
     * @return
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     *
     * @return Symfony\Component\HttpFoundation\JsonResponse
     */
    public function startAction()
    {
        $this->init();

        try {
            $this->checkIfUserActiveInAGame();

            list($game, $user) = $this->gameService->initializeGame($this->userId);

            $this->setContext(ContextKey::GAME_ID, $game->getId());

        } catch (\Exception $e) {

            return $this->handleException($e);
        }

        return new Response\Ok(
            [
                "game" => $game->toArray(),
                "user" => $user->toArray(),
            ]
        );
    }

    /**
     *
     * @return Symfony\Component\HttpFoundation\JsonResponse
     */
    public function indexAction()
    {
        $this->init();

        try {
            list($game, $user) = $this->gameService->fetchByIdAndValidateAgainsUser(
                $this->gameId,
                $this->userId
            );

        } catch (\Exception $e) {

            return $this->handleException($e);
        }

        return new Response\Ok(
            [
                "game" => $game->toArray(),
                "user" => $user->toArray(),
            ]
        );
    }

    /**
     *
     * @param string $gameId
     * @param string $atSN
     *
     * @return Symfony\Component\HttpFoundation\JsonResponse
     *
     */
    public function joinAction(
        string $gameId,
        string $atSN)
    {
        $this->init();

        try {

            $this->checkIfUserActiveInAGame();

            list($game, $user) = $this->gameService->joinGame(
                $gameId,
                $atSN,
                $this->userId
            );

            $this->setContext(ContextKey::GAME_ID, $game->getId());
        } catch (\Exception $e) {

            return $this->handleException($e);
        }

        return new Response\Ok(
            [
                "game" => $game->toArray(),
                "user" => $user->toArray(),
            ]
        );

    }


    /**
     * Attempts to move a card from `fromUserId` to session user
     *
     * @param string $card
     * @param string $fromUserId
     *
     * @return Symfony\Component\HttpFoundation\JsonResponse
     */
    public function moveFromAction(
        string $card,
        string $fromUserId)
    {
        $this->init();

        try {
            list($game, $user) = $this->gameService->fetchByIdAndValidateAgainsUser(
                $this->gameId,
                $this->userId
            );

            list($success, $game, $user) = $this->gameService->moveCard(
                $game,
                $card,
                $fromUserId,
                $this->userId
            );

        } catch (\Exception $e) {

            return $this->handleException($e);
        }

        return new Response\Ok(
            [
                "success" => $success,
                "game"    => $game->toArray(),
                "user"    => $user->toArray(),
            ]
        );
    }

    /**
     * @param string $id
     *
     * @return Symfony\Component\HttpFoundation\JsonResponse
     */
    public function deleteAction()
    {
        $this->init();

        try {
            $this->gameService->delete($this->gameId);

            $this->resetContext();

        } catch (\Exception $e) {

            return $this->handleException($e);
        }

        return new Response\Ok(
            [
                "success" => true
            ]
        );
    }
}
