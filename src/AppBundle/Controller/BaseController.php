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

    protected $gameId = null;
    protected $userId = null;

    protected $game   = null;
    protected $user   = null;

    // ----- Protected methods -----

    protected function init()
    {
        //
        // Init services
        //

        $this->logger      = $this->container->get('logger');
        $this->request     = $this->container->get('request_stack')->getCurrentRequest();
        $this->session     = $this->request->getSession();
        $this->gameService = $this->container->get(Service::GAME);

        //
        // Parses request payload
        //

        $this->input       = [];

        $contentType       = $this->request->headers->get('Content-Type');
        $isJson            = (strtolower($contentType) === self::JSON);

        if ($isJson)
        {
            $this->input   = json_decode($this->request->getContent(), true);
        }

        //
        // Gets game and user model
        //

        $this->gameId = $this->session->get(ContextKey::GAME_ID, false);

        if ($this->gameId)
        {
            try
            {
                $this->game = $this->gameService->get($this->gameId);
            }
            catch (NotFoundException $e)
            {
                $this->setContext(ContextKey::GAME_ID, false);
            }
        }

        $this->userId = $this->session->getId();

        if ($this->userId)
        {
            try
            {
                $this->user = $this->gameService->getUser($this->userId);
            }
            catch (NotFoundException $e)
            {
            }
        }
    }

    protected function ensureNoGame()
    {
        if (empty($this->game) === false)
        {
            throw new BadRequestException('A game exists in session already.', ['gameId' => $this->game->id]);
        }
    }

    protected function ensureGameAndUser()
    {
        if (empty($this->game))
        {
            throw new BadRequestException('No game exists in session.');
        }

        if (empty($this->user))
        {
            throw new BadRequestException('No details found for session user.', ['userId' => $this->userId]);
        }

        if ($this->game->hasUser($this->user->id) === false)
        {
            throw new BadRequestException('Session user does not belong the game.', ['gameId' => $this->game->id, 'userId' => $this->user->id]);
        }
    }

    protected function setContext(string $key, string $value)
    {
        $this->session->set($key, $value);
    }

    protected function resetContext()
    {
        $this->session->remove(ContextKey::GAME_ID);
        $this->session->invalidate();
    }
}
