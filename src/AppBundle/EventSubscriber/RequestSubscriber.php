<?php

namespace AppBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 *
 */
class RequestSubscriber implements EventSubscriberInterface
{

    /**
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
           KernelEvents::REQUEST => [
               ["ensureSessionStarted", 0],
           ],
        ];
    }

    /**
     *
     * @param object $logger
     *
     * @return
     */
    public function __construct($logger)
    {
        $this->logger = $logger;
    }

    /**
     *
     * @param GetResponseEvent $event
     *
     * @return
     */
    public function ensureSessionStarted(GetResponseEvent $event)
    {
        $session = $event->getRequest()->getSession();

        if ($session->isStarted() === false) {
            $session->start();
        }
    }
}
