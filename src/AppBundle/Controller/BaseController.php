<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Constants\SessionKey;


/**
 *
 */
class BaseController extends Controller
{

    /**
     *
     * @return
     */
    public function __construct()
    {
    }

    // ----- Protected methods -----

    /**
     * If user already engaged in a active game,
     *   redirects him to that page.
     * Cleans redis data if game associated is not active
     *
     * @return null|RedirectResponse
     */
    protected function redirectIfUserActiveInAGame()
    {

        $request     = $this->container->get('request_stack')->getCurrentRequest();
        $session     = $request->getSession();
        $gameId      = $session->get(SessionKey::GAME_ID, false);
        $userId      = $session->get(SessionKey::USER_ID, false);

        if ($gameId === false) {

            return;
        }

        $gameService = $this->container->get(Service::GAME);
        $game        = $gameService->fetchById($gameId);

        if ($game && $game->isActive() && $game->hasUser($userId)) {

            return new RedirectResponse(
                $this->generateUrl("game_index_id", ["id" => $gameId])
            );
        }

        if ($game && $game->isActive() === false) {
            $gameService->delete($game);
        }
    }

    // ----- -----
}
