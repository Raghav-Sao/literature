<?php

namespace AppBundle\Controller;

use AppBundle\Constant\Game;
use AppBundle\Controller\Response;
use AppBundle\Exception\NotFoundException;
use AppBundle\Exception\BadRequestException;

class ChatController extends BaseController
{
    public function postAction()
    {
        $this->init();

        $this->ensureGameAndUser();

        $message = ($this->input['message']) ?? null;

        if (empty($message))
        {
            throw new BadRequestException('No message provided in input');
        }

        $payload = [
            'user'    => $this->user->id,
            'message' => $message,
        ];

        $pusher = $this->container->get('app_bundle.pubsub.pusher');

        $pusher->trigger($this->gameId, Game\Event::CHAT_MESSAGE, $payload);

        return new Response\Ok(['message' => $message]);
    }
}
