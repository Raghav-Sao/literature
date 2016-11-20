<?php

namespace AppBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

use PHPUnit\Framework\TestCase;

/**
 *
 */
class ExceptionSubscriber implements EventSubscriberInterface
{
    /**
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
           KernelEvents::EXCEPTION => [
               ["processException", 10],
               ["logException",      0],
               ["notifyException", -10],
           ],
        ];
    }

    /**
     * @param object $logger
     *
     * @return
     */
    public function __construct($logger)
    {
        $this->logger = $logger;
    }

    /**
     * @codeCoverageIgnore
     *
     * @param GetResponseForExceptionEvent $event
     *
     * @return
     */
    public function processException(GetResponseForExceptionEvent $event)
    {
        // ...
    }

    /**
     * @codeCoverageIgnore
     *
     * @param GetResponseForExceptionEvent $event
     *
     * @return
     */
    public function logException(GetResponseForExceptionEvent $event)
    {
        // ...
    }

    /**
     * @codeCoverageIgnore
     *
     * @param GetResponseForExceptionEvent $event
     *
     * @return
     */
    public function notifyException(GetResponseForExceptionEvent $event)
    {
        // ...
    }
}
