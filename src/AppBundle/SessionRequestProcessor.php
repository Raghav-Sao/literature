<?php

namespace AppBundle;

use Symfony\Component\HttpFoundation\Session\Session;

use AppBundle\Constant\ContextKey;

class SessionRequestProcessor
{
    private $session;
    private $token;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function processRecord(array $record)
    {
        $debug = [
            '_u_id' => $this->session->get(ContextKey::USER_ID),
            '_g_id' => $this->session->get(ContextKey::GAME_ID),
        ];

        $record['extra'] += $debug;

        return $record;
    }
}
