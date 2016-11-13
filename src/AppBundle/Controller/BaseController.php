<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;

use AppBundle\Constants\Service;
use AppBundle\Constants\SessionKey;

use AppBundle\Exceptions\NotFoundException;
use AppBundle\Exceptions\BadRequestException;

/**
 *
 */
class BaseController extends Controller
{

    protected $request;
    protected $session;
    protected $userId;
    protected $gameId;

    /**
     *
     * @return
     */
    public function __construct()
    {
    }

    // ----- Protected methods -----

    /**
     *
     * @return
     */
    protected function init()
    {
        $this->logger      = $this->container->get('logger');
        $this->request     = $this->container->get('request_stack')->getCurrentRequest();
        $this->session     = $this->request->getSession();

        $this->gameId      = $this->session->get(SessionKey::GAME_ID, false);
        $this->userId      = $this->session->getId();

        $this->gameService = $this->container->get(Service::GAME);
    }

    /**
     * If user already engaged in a active game,
     *   redirects him to that page.
     * Cleans redis data if game associated is not active
     *
     * @return null|RedirectResponse
     */
    protected function redirectIfUserActiveInAGame()
    {

        if ($this->gameId === false) {

            return;
        }

        $game = $this->gameService->fetchById($this->gameId);

        // var_dump($game->hasUser($this->userId));die;

        if ($game && $game->isActive() && $game->hasUser($this->userId)) {

            throw new BadRequestException("You are already in an active game.");
            
        }

        if ($game && $game->isActive() === false) {
            $this->gameService->delete($game);
        }
    }

    /**
     * Handles controller catchec exceptions
     *
     * @param \Exception $e
     *
     * @return JsonResponse
     */
    protected function handleException(\Exception $e)
    {
        $this->logger->error($e);

        switch (get_class($e)) {

            case "AppBundle\Exceptions\BadRequestException":
                return $this->badRequest($e);
                break;

            case "AppBundle\Exceptions\NotFoundException":
                return $this->notFound($e);
                break;

            default:
                return $this->internalError($e);
                break;
        }
    }

    /**
     * Sends a 404 response
     *
     * @param string $message
     *
     * @return JsonResponse
     */
    protected function notFound(
        string $message = "Not Found.")
    {

        return new JsonResponse(["message" => $message], JsonResponse::HTTP_NOT_FOUND);
    }

    /**
     * Sends a 400 response
     *
     * @param string $message
     *
     * @return JsonResponse
     */
    protected function badRequest(
        string $message = "Bad Request.")
    {

        return new JsonResponse(["message" => $message], JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * Sends a 500 response
     *
     * @param string $message
     *
     * @return JsonResponse
     */
    protected function internalError(
        string $message = "Internal Error.")
    {

        return new JsonResponse(["message" => $message], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
    }

    // ----- -----
}
