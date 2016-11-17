<?php

namespace AppBundle\Controller;

use AppBundle\Constant\Game;
use AppBundle\Controller\Response;
use AppBundle\Exception;

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
    public function postAction()
    {
        $this->init();

        try {
            if ($this->gameId === false) {

                throw new Exception\NotFoundException("Game not found");
            }

            if (empty($this->input["message"])) {

                throw new Exception\BadRequestException("No message provided in input");
            }

            $this->container->get("app_bundle.pubsub.pusher")->trigger(
                $this->gameId,
                Game\Event::CHAT_MESSAGE,
                $this->input["message"]
            );

        } catch (\Exception $e) {

            return $this->handleException($e);
        }

        return new Response\Ok();
    }
}
