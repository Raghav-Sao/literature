<?php

namespace AppBundle\Controller;

use AppBundle\Constant\Game;
use AppBundle\Controller\Response;
use AppBundle\Exception\NotFoundException;
use AppBundle\Exception\BadRequestException;

class ChatController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function postAction()
    {
        $this->init();

        if ($this->gameId === false)
        {
            throw new NotFoundException('Game not found');
        }

        if (empty($this->input['message']))
        {
            throw new BadRequestException('No message provided in input');
        }

        $payload = [
            'user'    => $this->userId,
            'message' => $this->input['message'],
        ];

        $pusher = $this->container->get('app_bundle.pubsub.pusher');

        $pusher->trigger(
            $this->gameId,
            Game\Event::CHAT_MESSAGE,
            $payload
        );

        return new Response\Ok();
    }
}
