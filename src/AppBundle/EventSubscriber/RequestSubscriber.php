<?php

namespace AppBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RequestSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        $subscribedEvents = [
           KernelEvents::REQUEST => [
               ['ensureSessionStarted', 0],
           ],
        ];

        return $subscribedEvents;
    }

    public function __construct(
        $logger
    )
    {
        $this->logger = $logger;
    }

    public function ensureSessionStarted(
        GetResponseEvent $event
    )
    {
        $session = $event->getRequest()->getSession();

        if ($session->isStarted() === false)
        {
            $session->start();
        }
    }
}
