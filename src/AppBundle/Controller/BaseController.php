<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use AppBundle\Constant\Service;
use AppBundle\Constant\ContextKey;
use AppBundle\Exception\NotFoundException;
use AppBundle\Exception\BadRequestException;
use AppBundle\Controller\Response;


class BaseController extends Controller
{
    const JSON = 'application/json';

    protected $logger;
    protected $request;
    protected $session;

    protected $gameService;

    protected $userId;
    protected $gameId;

    // ----- Protected methods -----

    protected function init()
    {
        $this->logger      = $this->container->get('logger');
        $this->request     = $this->container->get('request_stack')
                                             ->getCurrentRequest();
        $this->session     = $this->request->getSession();

        $this->gameService = $this->container->get(Service::GAME);

        $this->gameId      = $this->session->get(ContextKey::GAME_ID, false);
        $this->userId      = $this->session->getId();

        $this->input       = [];

        $contentType       = $this->request->headers->get('Content-Type');
        $isJson            = (strtolower($contentType) === self::JSON);

        if ($isJson)
        {
            $this->input   = json_decode($this->request->getContent(), true);
        }
    }

    protected function checkIfUserActiveInAGame()
    {
        if ($this->gameId === false)
        {
            return;
        }

        $game = $this->gameService->get($this->gameId);

        if ($game && $game->isNotExpired() && $game->hasUser($this->userId))
        {
            throw new BadRequestException(
                'You are already in an active game',
                ['gameId' => $this->gameId]
            );
        }

        if ($game && $game->isExpired())
        {
            $this->gameService->delete($game);
        }
    }

    protected function setContext(
        string $key,
        string $value
    )
    {
        $this->session->set($key, $value);
    }

    protected function resetContext()
    {
        $this->session->remove(ContextKey::GAME_ID);
        $this->session->invalidate();
    }
}
