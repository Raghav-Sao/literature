<?php

namespace AppBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

use AppBundle\Utility;

class ResponseSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        $subscribedEvents = [
           KernelEvents::RESPONSE => [
               ['log', 0],
           ],
        ];

        return $subscribedEvents;
    }

    public function __construct($logger)
    {
        $this->logger = $logger;
    }

    public function log(FilterResponseEvent $event)
    {
        //
        // TODO:
        // - Do not log everything
        //

        $this->logger->debug($event->getResponse()->__toString());
    }
}
