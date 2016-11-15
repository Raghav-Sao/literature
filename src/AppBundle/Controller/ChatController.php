<?php

namespace AppBundle\Controller;

use AppBundle\Constant\Game;
use AppBundle\Controller\Response;
use AppBundle\Exception\NotFoundException;

/**
 *
 */
class ChatController extends BaseController
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
    public function post()
    {
        $this->init();

        if ($this->gameId === false) {

            throw new NotFoundException("Game not found.");
        }

        // $this->container->get("app_bundle.pubsub.pusher")->trigger(
        //     $this->gameId,
        //     Game\Event::CHAT_MESSAGE,
        //     $this->request->request->get("message")
        // );

        return new Response\Ok();
    }
}
