<?php

namespace AppBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

use AppBundle\Utility;

class RequestSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        $subscribedEvents = [
           KernelEvents::REQUEST => [
               ['ensureSessionStarted', 0],
               ['ensureCidHeader',      1],
               ['log',                  3],
           ],
        ];

        return $subscribedEvents;
    }

    public function __construct($logger)
    {
        $this->logger = $logger;
    }

    public function ensureSessionStarted(GetResponseEvent $event)
    {
        $session = $event->getRequest()->getSession();

        if ($session->isStarted() === false)
        {
            $session->start();
        }
    }

    public function ensureCidHeader(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $headers = $request->headers;

        if ($headers->has('cid'))
        {
            return;
        }

        $headers->set('cid', Utility::generateId('cid'));
    }

    public function log(GetResponseEvent $event)
    {
        //
        // TODO:
        // - Do not log everything
        //

        $this->logger->debug($event->getRequest()->__toString());
    }
}
