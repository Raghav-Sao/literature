<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;

use AppBundle\Constants\Service;
use AppBundle\Constants\SessionKey;


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
        $this->request     = $this->container->get('request_stack')->getCurrentRequest();
        $this->session     = $this->request->getSession();

        $this->gameId      = $this->session->get(SessionKey::GAME_ID, false);
        $this->userId      = $this->session->get(SessionKey::USER_ID, false);

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

        $game        = $this->gameService->fetchById($gameId);

        if ($game && $game->isActive() && $game->hasUser($this->userId)) {

            return new RedirectResponse(
                $this->generateUrl("game_index_id", ["id" => $this->gameId])
            );
        }

        if ($game && $game->isActive() === false) {
            $this->gameService->delete($game);
        }
    }

    /**
     * Sends a 404 response
     *
     * @param string $message
     *
     * @return JsonResponse
     */
    protected function notFound(string $message)
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
    protected function badRequest(string $message)
    {

        return new JsonResponse(["message" => $message], JsonResponse::HTTP_BAD_REQUEST);
    }

    // ----- -----
}
