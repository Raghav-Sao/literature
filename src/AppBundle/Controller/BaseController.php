<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use AppBundle\Constant\Service;
use AppBundle\Constant\ContextKey;

use AppBundle\Exception\NotFoundException;
use AppBundle\Exception\BadRequestException;

use AppBundle\Controller\Response;

/**
 *
 */
class BaseController extends Controller
{
    const APPLICATION_JSON = 'application/json';

    protected $logger;
    protected $request;
    protected $session;
    protected $gameService;

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
        $this->logger      = $this->container->get("logger");
        $this->request     = $this->container->get("request_stack")->getCurrentRequest();
        $this->session     = $this->request->getSession();
        $this->gameService = $this->container->get(Service::GAME);

        $this->gameId      = $this->session->get(ContextKey::GAME_ID, false);
        $this->userId      = $this->session->getId();

        $requestContentType       = $this->request->headers->get('Content-Type');
        $isRequestContentTypeJson = (strtolower($requestContentType) === self::APPLICATION_JSON);

        if ($isRequestContentTypeJson) {
            $this->input = json_decode($this->request->getContent(), true);
        } else {
            $this->input = [];
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
        switch (get_class($e)) {
            case "AppBundle\Exception\BadRequestException":
                return new Response\Error($e, Response\Error::HTTP_BAD_REQUEST);
                break;

            case "AppBundle\Exception\NotFoundException":
                return new Response\Error($e, Response\Error::HTTP_NOT_FOUND);
                break;

            default:
                $this->logger->error($e);

                return new Response\Error($e, Response\Error::HTTP_INTERNAL_SERVER_ERROR);
                break;
        }
    }

    // ----- -----



    //

    /**
     * If user already engaged in a active game,
     *   redirects him to that page.
     * Cleans redis data if game associated is not active
     *
     * @return
     */
    protected function checkIfUserActiveInAGame()
    {
        if ($this->gameId === false) {
            return;
        }

        $game = $this->gameService->fetchGameById($this->gameId);

        if ($game && $game->isExpired() === false && $game->hasUser($this->userId)) {
            throw new BadRequestException("You are already in an active game", ["gameId" => $this->gameId]);
        }

        if ($game && $game->isExpired()) {
            $this->gameService->delete($game);
        }
    }

    /**
     * Set key/values in context. Currently it is session.
     *
     * @param string $key
     * @param string $value
     *
     * @return
     */
    protected function setContext(
        string $key,
        string $value
    ) {

        $this->session->set($key, $value);
    }

    /**
     * @return
     */
    protected function resetContext()
    {
        $this->session->remove(ContextKey::GAME_ID);
        $this->session->invalidate();
    }
}
