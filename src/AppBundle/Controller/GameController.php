<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use AppBundle\Constants\Service;
use AppBundle\Constants\SessionKey;
use AppBundle\Utility;

use AppBundle\Exceptions\BadRequestException;

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
    }

    /**
     *
     * @return Response
     */
    public function startAction()
    {
        $this->init();

        try {

            $this->checkIfUserActiveInAGame();

            list($game, $user) = $this->gameService->initializeGame($this->userId);

            $this->session->set(SessionKey::GAME_ID, $game->getId());

        } catch (\Exception $e) {

            return $this->handleException($e);
        }

        return new JsonResponse([
            "response" => [
                "game" => $game->toArray(),
                "user" => $user->toArray(),
            ]
        ]);
    }

    /**
     *
     * @return JsonResponse
     */
    public function indexAction($id)
    {
        $this->init();

        try {

            $game = $this->gameService->fetchById($id);

            $this->gameService->validateGame($game, $this->userId);

            $user = $this->gameService->fetchUserById($this->userId);

        } catch (\Exception $e) {

            return $this->handleException($e);
        }

        return new JsonResponse([
            "response" => [
                "game" => $game->toArray(),
                "user" => $user->toArray(),
            ]
        ]);
    }

    /**
     *
     * @param string $gameId
     * @param string $userSN
     *
     * @return string
     *
     */
    public function joinAction($gameId, $userSN)
    {
        $this->init();
        try {
            
            $this->checkIfUserActiveInAGame();

            $game = $this->gameService->fetchById($gameId);

            $this->gameService->validateGame($game);

            list($game, $user) = $this->gameService->join(
                $game, 
                $userSN, 
                $this->userId
            );

            $this->session->set(SessionKey::GAME_ID, $game->getId());

            return new JsonResponse([
                "response" => [
                    "game" => $game->toArray(),
                    "user" => $user->toArray(),
                ]
            ]);
        } catch (\Exception $e) {

            return $this->handleException($e);
        }

    }


    /**
     * @param string $id
     * @param string $card
     * @param string $fromUserSN
     * @param string $toUserSN
     */
    public function moveCardAction(
        string $id,
        string $card,
        string $fromUserSN,
        string $toUserSN)
    {

        // Using session data
        // $id       = $this->gameId
        // $toUserSN = $this->userId 's SN

        $this->init();

        try {

            $game = $this->gameService->fetchById($this->gameId);

            $this->gameService->validateGame($game, $this->userId);

            // Other validation
            if ($game->isValidSNAgainstID($toUserSN, $this->userId) === false) {
                throw new BadRequestException("`toUserSN` is not valid.");
            }

            $fromUser = $this->gameService->fetchUserById($game->getUserIdBySN($fromUserSN));
            $toUser   = $this->gameService->fetchUserById($game->getUserIdBySN($toUserSN));

            list($success, $message) = $this->gameService->moveCard(
                $game,
                $card,
                $fromUser,
                $toUser
            );

        } catch (\Exception $e) {

            return $this->handleException($e);
        }

        return new JsonResponse([
            "response" => [
                "success" => $success,
                "message" => $message,
                "game"    => $game->toArray(),
                "user"    => $toUser->toArray(),
            ]
        ]);
    }

    /**
     * @param string $id
     *
     * @return JsonResponse
     */
    public function deleteAction(
        string $id)
    {
        $this->init();

        try {

            $game = $this->gameService->fetchById($this->gameId);

            $this->gameService->validateGame($game, $this->userId);

            $this->gameService->delete($game);

            $this->session->remove(SessionKey::GAME_ID);
            $this->session->invalidate();

        } catch (\Exception $e) {

            return $this->handleException($e);
        }

        return new JsonResponse([
            "response" => [
                "success" => true
            ]
        ]);
    }
}
